import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/auth_provider.dart';
import '../providers/data_provider.dart';
import '../services/api_service.dart';
import 'job_history_screen.dart';
import 'broadcast_screen.dart';
import 'notification_screen.dart';
import 'admin/personel_list_screen.dart';
import 'admin/create_broadcast_screen.dart';
import 'admin/skep_verification_screen.dart';
import 'admin/registration_verification_screen.dart';
import 'admin/system_logs_screen.dart';
import 'admin/system_settings_screen.dart';
import 'admin/reports_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  Timer? _carouselTimer;
  Timer? _notificationPollingTimer;

  bool _isAdminOrCoordinator = false;
  Map<String, dynamic>? _adminStats;
  bool _loadingAdminStats = false;

  @override
  void initState() {
    super.initState();
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final role = auth.user?.role ?? 'personel';
    if (role == 'admin' || role == 'kordinator_matra' || role == 'kordinator_angkatan') {
      _isAdminOrCoordinator = true;
      _fetchAdminStats();
    } else {
      WidgetsBinding.instance.addPostFrameCallback((_) => _refreshData());
    }

    // Polling notifikasi dan data secara otomatis setiap 15 detik
    _notificationPollingTimer = Timer.periodic(const Duration(seconds: 15), (timer) {
      if (mounted) {
        final data = Provider.of<DataProvider>(context, listen: false);
        data.fetchNotifications();
        if (_isAdminOrCoordinator) {
          _fetchAdminStats();
        } else {
          data.fetchDashboard();
        }
      }
    });
  }

  Future<void> _fetchAdminStats() async {
    setState(() {
      _loadingAdminStats = true;
    });
    try {
      final token = Provider.of<AuthProvider>(context, listen: false).token;
      final api = ApiService(token: token);
      final response = await api.get('/admin/dashboard-stats');
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        setState(() {
          _adminStats = resData;
        });
      }
    } catch (_) {}
    setState(() {
      _loadingAdminStats = false;
    });
  }

  @override
  void dispose() {
    _carouselTimer?.cancel();
    _notificationPollingTimer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _refreshData() async {
    final data = Provider.of<DataProvider>(context, listen: false);
    await Future.wait([
      data.fetchDashboard(),
      data.fetchNotifications(),
    ]);
    
    // Inisialisasi timer carousel setelah data terisi
    _startCarouselTimer(data.latestBroadcasts.length);
  }

  void _startCarouselTimer(int pageCount) {
    _carouselTimer?.cancel();
    if (pageCount <= 1) return;

    _carouselTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      if (_pageController.hasClients) {
        int nextPage = _currentPage + 1;
        if (nextPage >= pageCount) {
          nextPage = 0;
        }
        _pageController.animateToPage(
          nextPage,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOutCubic,
        );
      }
    });
  }

  String _formatPangkat(String? pangkat) {
    if (pangkat == null || pangkat.isEmpty) return 'Prada';
    return pangkat.substring(0, 1).toUpperCase() + pangkat.substring(1).toLowerCase();
  }

  void _showProfileBottomSheet(BuildContext context, dynamic personel, dynamic auth) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(28),
              topRight: Radius.circular(28),
            ),
          ),
          padding: const EdgeInsets.all(28.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 50,
                  height: 5,
                  decoration: BoxDecoration(
                    color: Colors.grey[300],
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: const Color(0xff0F172A).withOpacity(0.05),
                      shape: BoxShape.circle,
                      border: Border.all(color: const Color(0xff0F172A).withOpacity(0.1), width: 1.5),
                    ),
                    child: const Icon(Icons.person_outline, size: 30, color: Color(0xff0F172A)),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${_formatPangkat(personel?.pangkat)} ${personel?.fullName ?? auth.user?.username}',
                          style: GoogleFonts.outfit(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xff0F172A),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'NIKC: ${personel?.nikc ?? '-'}',
                          style: const TextStyle(fontSize: 13, color: Color(0xff64748B)),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              const Divider(color: Color(0xffE2E8F0)),
              const SizedBox(height: 16),
              
              // Informasi Detail
              _buildProfileDetailRow('Matra', 'TNI ${personel?.matra ?? '-'}'),
              _buildProfileDetailRow('No HP', personel?.phoneNumber ?? '-'),
              _buildProfileDetailRow('NIKC', personel?.nikc ?? '-'),
              _buildProfileDetailRow('Status Personel', 'AKTIF', isStatus: true),
              
              const SizedBox(height: 28),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  _showLogoutConfirmation(context, auth);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xffEF4444),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 0,
                ),
                child: const Text('Keluar dari Aplikasi', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
              const SizedBox(height: 12),
            ],
          ),
        );
      },
    );
  }

  void _showLogoutConfirmation(BuildContext context, dynamic auth) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Konfirmasi Keluar'),
        content: const Text('Apakah Anda yakin ingin keluar dari akun Anda?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              auth.logout();
            },
            child: const Text('Keluar', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  List<Widget> _buildRoleBasedMenus(
    BuildContext context,
    String role,
    dynamic personel,
    AuthProvider auth,
  ) {
    if (role == 'admin') {
      return [
        _buildDjpMenuButton(
          context,
          title: 'Verifikasi SKEP',
          icon: Icons.assignment_turned_in,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const SkepVerificationScreen()),
            ).then((_) => _fetchAdminStats());
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Kirim Broadcast',
          icon: Icons.campaign,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const CreateBroadcastScreen()),
            ).then((_) => _fetchAdminStats());
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Master Personel',
          icon: Icons.people,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const PersonelListScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Notifikasi',
          icon: Icons.notifications,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Konfirmasi Daftar',
          icon: Icons.how_to_reg,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const RegistrationVerificationScreen()),
            ).then((_) => _fetchAdminStats());
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Monitor Log',
          icon: Icons.history,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const SystemLogsScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Pengaturan Sistem',
          icon: Icons.settings_applications,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const SystemSettingsScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Lapor Pelaporan',
          icon: Icons.analytics,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const ReportsScreen()),
            );
          },
        ),
      ];
    } else if (role == 'kordinator_angkatan' || role == 'kordinator_matra') {
      return [
        _buildDjpMenuButton(
          context,
          title: 'Kekuatan Jajaran',
          icon: Icons.people,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const PersonelListScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Kirim Broadcast',
          icon: Icons.campaign,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const CreateBroadcastScreen()),
            ).then((_) => _fetchAdminStats());
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Notifikasi',
          icon: Icons.notifications,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Profil',
          icon: Icons.contact_page,
          color: const Color(0xff1E3A8A),
          onPressed: () => _showProfileBottomSheet(context, personel, auth),
        ),
      ];
    } else {
      return [
        _buildDjpMenuButton(
          context,
          title: 'Pekerjaan',
          icon: Icons.business_center,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const JobHistoryScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Presensi',
          icon: Icons.assignment_turned_in,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const BroadcastScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Notifikasi',
          icon: Icons.email,
          color: const Color(0xff1E3A8A),
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationScreen()),
            );
          },
        ),
        _buildDjpMenuButton(
          context,
          title: 'Profil',
          icon: Icons.contact_page,
          color: const Color(0xff1E3A8A),
          onPressed: () => _showProfileBottomSheet(context, personel, auth),
        ),
      ];
    }
  }

  Widget _buildAdminStatsWidget(Map<String, dynamic>? statsData, String role) {
    if (_loadingAdminStats || statsData == null) {
      return const Padding(
        padding: EdgeInsets.all(24.0),
        child: Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(Color(0xff1E3A8A)))),
      );
    }

    final stats = statsData['stats'] ?? {};
    final total = stats['total_personel'] ?? 0;
    final aktif = stats['total_aktif'] ?? 0;
    final verified = stats['total_verified'] ?? 0;
    final pendingSkep = stats['pending_skep'] ?? 0;

    final matraDist = statsData['matra_distribution'] ?? {};
    final ad = matraDist['AD'] ?? 0;
    final al = matraDist['AL'] ?? 0;
    final au = matraDist['AU'] ?? 0;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Statistik Kekuatan Jajaran',
            style: GoogleFonts.outfit(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: const Color(0xff0F172A),
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(child: _buildStatCard('Total Jajaran', total.toString(), Icons.people, const Color(0xff1E3A8A))),
              const SizedBox(width: 14),
              Expanded(child: _buildStatCard('Anggota Aktif', aktif.toString(), Icons.check_circle, const Color(0xff10B981))),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(child: _buildStatCard('Terverifikasi Wajah', verified.toString(), Icons.face, const Color(0xff0EA5E9))),
              if (role == 'admin') ...[
                const SizedBox(width: 14),
                Expanded(child: _buildStatCard('SKEP Pending', pendingSkep.toString(), Icons.assignment_late, const Color(0xffEF4444))),
              ],
            ],
          ),
          const SizedBox(height: 24),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: const Color(0xffE2E8F0)),
              boxShadow: [
                BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 4)),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Distribusi Matra Jajaran',
                  style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                ),
                const SizedBox(height: 16),
                _buildMatraBar('TNI AD (Angkatan Darat)', ad, total, const Color(0xff16A34A)),
                const SizedBox(height: 12),
                _buildMatraBar('TNI AL (Angkatan Laut)', al, total, const Color(0xff1E3A8A)),
                const SizedBox(height: 12),
                _buildMatraBar('TNI AU (Angkatan Udara)', au, total, const Color(0xff0EA5E9)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xffE2E8F0)),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.01), blurRadius: 6, offset: const Offset(0, 3)),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontSize: 10, color: Color(0xff64748B))),
                const SizedBox(height: 4),
                Text(value, style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold, color: const Color(0xff0f172a))),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMatraBar(String matra, int count, int total, Color color) {
    final pct = total > 0 ? (count / total) : 0.0;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(matra, style: const TextStyle(fontSize: 11, color: Color(0xff64748B))),
            Text('$count personel (${(pct * 100).toStringAsFixed(1)}%)', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xff1e293b))),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: pct,
            backgroundColor: const Color(0xffF1F5F9),
            valueColor: AlwaysStoppedAnimation(color),
            minHeight: 8,
          ),
        ),
      ],
    );
  }

  Widget _buildProfileDetailRow(String label, String value, {bool isStatus = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 14, color: Color(0xff64748B))),
          isStatus
              ? Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xff10B981).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    value,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: Color(0xff10B981),
                    ),
                  ),
                )
              : Text(
                  value,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xff1E293B)),
                ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final data = Provider.of<DataProvider>(context);
    final personel = auth.user?.personel;
    final role = auth.user?.role ?? 'personel';

    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(70),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            border: Border(
              bottom: BorderSide(
                color: const Color(0xff0F172A).withOpacity(0.06),
                width: 1.5,
              ),
            ),
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Sisi Kiri: Logo & Nama Instansi
                  Row(
                    children: [
                      Container(
                        height: 38,
                        width: 38,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          image: DecorationImage(
                            image: AssetImage('assets/icon/app_icon.png'),
                            fit: BoxFit.contain,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'SISFOPERSKC',
                        style: GoogleFonts.outfit(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xff0F172A),
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
                  ),
                  
                  // Sisi Kanan: Notifikasi, Bahasa & Avatar
                  Row(
                    children: [
                      // Lonceng
                      Stack(
                        clipBehavior: Clip.none,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.notifications_none_outlined, color: Color(0xff0F172A), size: 24),
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const NotificationScreen()),
                              );
                            },
                          ),
                          if (data.unreadNotificationsCount > 0)
                            Positioned(
                              right: 6,
                              top: 6,
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: const BoxDecoration(
                                  color: Colors.red,
                                  shape: BoxShape.circle,
                                ),
                                constraints: const BoxConstraints(
                                  minWidth: 16,
                                  minHeight: 16,
                                ),
                                child: Text(
                                  '${data.unreadNotificationsCount}',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 8,
                                    fontWeight: FontWeight.bold,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(width: 4),
                      
                      // Bahasa "ID"
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          border: Border.all(color: const Color(0xffE2E8F0)),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Row(
                          children: [
                            Text(
                              'ID',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: Color(0xff0F172A),
                              ),
                            ),
                            Icon(Icons.keyboard_arrow_down, size: 14, color: Color(0xff64748B)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 10),
                      
                      // Avatar Kotak Membulat
                      GestureDetector(
                        onTap: () => _showProfileBottomSheet(context, personel, auth),
                        child: Container(
                          width: 38,
                          height: 38,
                          decoration: BoxDecoration(
                            border: Border.all(color: const Color(0xffE2E8F0)),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.person_outline, size: 20, color: Color(0xff0F172A)),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _refreshData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Area Sambutan (CoreTax Style)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 24.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    // Brand Logo Area
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          'SISFO',
                          style: GoogleFonts.outfit(
                            fontSize: 28,
                            fontWeight: FontWeight.w900,
                            color: const Color(0xff1E3A8A),
                          ),
                        ),
                        Text(
                          'PERS',
                          style: GoogleFonts.outfit(
                            fontSize: 28,
                            fontWeight: FontWeight.w900,
                            color: const Color(0xffEAB308),
                          ),
                        ),
                        Text(
                          'KC',
                          style: GoogleFonts.outfit(
                            fontSize: 28,
                            fontWeight: FontWeight.w900,
                            color: const Color(0xff1E3A8A),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Selamat Datang Kembali,',
                      style: TextStyle(
                        fontSize: 14,
                        color: Color(0xff64748B),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${role == 'admin' ? 'Administrator' : _formatPangkat(personel?.pangkat)} ${personel?.fullName ?? auth.user?.username}',
                      style: GoogleFonts.outfit(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: const Color(0xff0F172A),
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),

              // Grid Layanan Utama (Dinamis Sesuai Peran dengan Wrap Layout Premium)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                child: Wrap(
                  alignment: WrapAlignment.spaceEvenly,
                  spacing: 12,
                  runSpacing: 12,
                  children: _buildRoleBasedMenus(context, role, personel, auth),
                ),
              ),
              const SizedBox(height: 24),

              // Search Bar "Cari layanan..."
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20.0),
                child: Container(
                  height: 48,
                  decoration: BoxDecoration(
                    color: const Color(0xffF1F5F9),
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: const Color(0xffE2E8F0)),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 16.0),
                  child: const Row(
                    children: [
                      Icon(Icons.search, color: Color(0xff94A3B8), size: 20),
                      SizedBox(width: 10),
                      Text(
                        'Cari riwayat atau pengumuman...',
                        style: TextStyle(
                          color: Color(0xff94A3B8),
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 28),

              // Kontainer Pengumuman Terbaru / Statistik Jajaran Eksekutif
              _isAdminOrCoordinator
                  ? _buildAdminStatsWidget(_adminStats, role)
                  : Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 20.0),
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: const Color(0xffE2E8F0)),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.03),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  padding: const EdgeInsets.all(20.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Header Pengumuman
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xffFEF9C3),
                              shape: BoxShape.circle,
                              border: Border.all(color: const Color(0xffFEF08A)),
                            ),
                            child: const Icon(
                              Icons.campaign,
                              color: Color(0xffCA8A04),
                              size: 22,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Pengumuman Terbaru',
                                  style: GoogleFonts.outfit(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xff0F172A),
                                  ),
                                ),
                                const SizedBox(height: 4),
                                const Text(
                                  'Pengumuman dan informasi penting dari SISFOPERSKC.',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Color(0xff64748B),
                                  ),
                                ),
                                const SizedBox(height: 6),
                                const Text(
                                  'Banner berganti otomatis. Geser untuk membaca.',
                                  style: TextStyle(
                                    fontSize: 10,
                                    color: Color(0xff94A3B8),
                                    fontStyle: FontStyle.italic,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Carousel Slider
                      data.isLoading
                          ? const Center(
                              child: Padding(
                                padding: EdgeInsets.all(32.0),
                                child: CircularProgressIndicator(),
                              ),
                            )
                          : data.latestBroadcasts.isEmpty
                              ? Container(
                                  padding: const EdgeInsets.symmetric(vertical: 32),
                                  child: const Column(
                                    children: [
                                      Icon(Icons.info_outline, color: Color(0xff94A3B8), size: 32),
                                      SizedBox(height: 10),
                                      Text(
                                        'Belum ada pengumuman kegiatan baru.',
                                        style: TextStyle(color: Color(0xff64748B), fontSize: 12),
                                      ),
                                    ],
                                  ),
                                )
                              : Column(
                                  children: [
                                    SizedBox(
                                      height: 140,
                                      child: PageView.builder(
                                        controller: _pageController,
                                        onPageChanged: (int index) {
                                          setState(() {
                                            _currentPage = index;
                                          });
                                        },
                                        itemCount: data.latestBroadcasts.length,
                                        itemBuilder: (context, index) {
                                          final item = data.latestBroadcasts[index];
                                          return Padding(
                                            padding: const EdgeInsets.symmetric(horizontal: 4.0),
                                            child: Container(
                                              padding: const EdgeInsets.all(16),
                                              decoration: BoxDecoration(
                                                color: const Color(0xffF8FAFC),
                                                borderRadius: BorderRadius.circular(16),
                                                border: Border.all(color: const Color(0xffF1F5F9)),
                                              ),
                                              child: Column(
                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                children: [
                                                  Row(
                                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                                    children: [
                                                      Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                        decoration: BoxDecoration(
                                                          color: const Color(0xffE2E8F0),
                                                          borderRadius: BorderRadius.circular(6),
                                                        ),
                                                        child: Text(
                                                          item.matra == 'ALL' ? 'UMUM' : 'TNI ${item.matra}',
                                                          style: const TextStyle(
                                                            fontSize: 9,
                                                            fontWeight: FontWeight.bold,
                                                            color: Color(0xff475569),
                                                          ),
                                                        ),
                                                      ),
                                                      if (item.eventDate != null)
                                                        Text(
                                                          item.eventDate!,
                                                          style: const TextStyle(
                                                            fontSize: 10,
                                                            color: Color(0xff64748B),
                                                          ),
                                                        ),
                                                    ],
                                                  ),
                                                  const SizedBox(height: 10),
                                                  Text(
                                                    item.title,
                                                    style: GoogleFonts.outfit(
                                                      fontSize: 14,
                                                      fontWeight: FontWeight.bold,
                                                      color: const Color(0xff1E293B),
                                                    ),
                                                    maxLines: 1,
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                  const SizedBox(height: 6),
                                                  Expanded(
                                                    child: Text(
                                                      item.description,
                                                      style: const TextStyle(
                                                        fontSize: 11,
                                                        color: Color(0xff64748B),
                                                        height: 1.4,
                                                      ),
                                                      maxLines: 3,
                                                      overflow: TextOverflow.ellipsis,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          );
                                        },
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    
                                    // Dots Indicator
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: List.generate(
                                        data.latestBroadcasts.length,
                                        (index) => Container(
                                          margin: const EdgeInsets.symmetric(horizontal: 4),
                                          width: _currentPage == index ? 16 : 6,
                                          height: 6,
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(3),
                                            color: _currentPage == index
                                                ? const Color(0xff1E3A8A)
                                                : const Color(0xffCBD5E1),
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDjpMenuButton(
    BuildContext context, {
    required String title,
    required IconData icon,
    required Color color,
    required VoidCallback onPressed,
  }) {
    return GestureDetector(
      onTap: onPressed,
      child: Container(
        width: 75,
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
          border: Border.all(color: const Color(0xffE2E8F0)),
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xffF1F5F9),
                shape: BoxShape.circle,
              ),
              child: Icon(
                icon,
                color: const Color(0xff1E3A8A),
                size: 22,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: GoogleFonts.outfit(
                fontSize: 10,
                fontWeight: FontWeight.bold,
                color: const Color(0xff0F172A),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
