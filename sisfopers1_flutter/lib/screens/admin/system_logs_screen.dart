import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/api_service.dart';

class SystemLogsScreen extends StatefulWidget {
  const SystemLogsScreen({super.key});

  @override
  State<SystemLogsScreen> createState() => _SystemLogsScreenState();
}

class _SystemLogsScreenState extends State<SystemLogsScreen> with SingleTickerProviderStateMixin {
  ApiService get _api => ApiService(token: Provider.of<AuthProvider>(context, listen: false).token);
  late TabController _tabController;
  List<dynamic> _auditLogs = [];
  List<dynamic> _loginLogs = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _fetchLogs();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchLogs() async {
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await _api.get('/admin/logs');
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        setState(() {
          _auditLogs = resData['audit_logs'] ?? [];
          _loginLogs = resData['login_logs'] ?? [];
        });
      }
    } catch (_) {}
    setState(() {
      _isLoading = false;
    });
  }

  String _formatDateTime(String? rawStr) {
    if (rawStr == null || rawStr.isEmpty) return '-';
    try {
      final dt = DateTime.parse(rawStr).toLocal();
      return '${dt.day.toString().padLeft(2, '0')}-${dt.month.toString().padLeft(2, '0')}-${dt.year} | ${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')} WIB';
    } catch (_) {
      return rawStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: AppBar(
        title: Text(
          'Monitor Log Sistem',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A), fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xff0F172A)),
        shape: Border(bottom: BorderSide(color: const Color(0xff0F172A).withOpacity(0.06), width: 1.5)),
        bottom: TabBar(
          controller: _tabController,
          labelColor: const Color(0xff1E3A8A),
          unselectedLabelColor: const Color(0xff64748B),
          indicatorColor: const Color(0xff1E3A8A),
          indicatorWeight: 3,
          labelStyle: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: const [
            Tab(text: 'Log Aktivitas Audit'),
            Tab(text: 'Log Masuk & Geolocation'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(Color(0xff1E3A8A))))
          : TabBarView(
              controller: _tabController,
              children: [
                _buildAuditLogsTab(),
                _buildLoginLogsTab(),
              ],
            ),
    );
  }

  Widget _buildAuditLogsTab() {
    if (_auditLogs.isEmpty) {
      return _buildEmptyState('Belum ada log aktivitas.');
    }
    return RefreshIndicator(
      onRefresh: _fetchLogs,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _auditLogs.length,
        itemBuilder: (context, index) {
          final log = _auditLogs[index];
          final action = log['action'] ?? 'ACTIVITY';
          Color actionColor = const Color(0xff64748B);
          if (action == 'LOGIN') actionColor = const Color(0xff10B981);
          if (action == 'CREATE' || action == 'APPROVE') actionColor = const Color(0xff0EA5E9);
          if (action == 'DELETE' || action == 'REJECT') actionColor = const Color(0xffEF4444);

          return Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xffE2E8F0)),
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
                        color: actionColor.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        action,
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: actionColor),
                      ),
                    ),
                    Text(
                      _formatDateTime(log['created_at']),
                      style: const TextStyle(fontSize: 10, color: Color(0xff94A3B8)),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  'Pengguna: ${log['full_name'] ?? log['username'] ?? 'Sistem'}',
                  style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xff1E293B)),
                ),
                const SizedBox(height: 4),
                Text(
                  'Modul: ${log['model_type']?.split('\\')?.last ?? '-'} (ID: ${log['model_id'] ?? '-'})',
                  style: const TextStyle(fontSize: 11, color: Color(0xff64748B)),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.laptop_chromebook, size: 12, color: Color(0xff94A3B8)),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        'IP: ${log['ip_address'] ?? '-'}',
                        style: const TextStyle(fontSize: 11, color: Color(0xff64748B)),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildLoginLogsTab() {
    if (_loginLogs.isEmpty) {
      return _buildEmptyState('Belum ada log masuk.');
    }
    return RefreshIndicator(
      onRefresh: _fetchLogs,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _loginLogs.length,
        itemBuilder: (context, index) {
          final log = _loginLogs[index];
          final browser = log['browser'] ?? 'Browser';
          final os = log['os'] ?? 'OS';
          final city = log['city'] ?? '';
          final province = log['province'] ?? '';
          final location = (city.isNotEmpty || province.isNotEmpty) ? '$city, $province' : 'Lokasi tidak diketahui';

          return Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xffE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.check_circle, size: 14, color: Color(0xff10B981)),
                        const SizedBox(width: 4),
                        const Text(
                          'LOGIN BERHASIL',
                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xff10B981)),
                        ),
                      ],
                    ),
                    Text(
                      _formatDateTime(log['login_at']),
                      style: const TextStyle(fontSize: 10, color: Color(0xff94A3B8)),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  'Pengguna: ${log['full_name'] ?? log['username'] ?? '-'}',
                  style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xff1E293B)),
                ),
                const SizedBox(height: 6),
                Row(
                  children: [
                    const Icon(Icons.phone_android, size: 12, color: Color(0xff64748B)),
                    const SizedBox(width: 4),
                    Text(
                      '$os | $browser',
                      style: const TextStyle(fontSize: 11, color: Color(0xff64748B)),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.location_on, size: 12, color: Color(0xffEF4444)),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        location,
                        style: const TextStyle(fontSize: 11, color: Color(0xff64748B)),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildEmptyState(String msg) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.history, size: 64, color: const Color(0xff94A3B8).withOpacity(0.5)),
          const SizedBox(height: 16),
          Text(
            'Log Kosong',
            style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold, color: const Color(0xff475569)),
          ),
          const SizedBox(height: 4),
          Text(
            msg,
            style: const TextStyle(fontSize: 12, color: Color(0xff64748B)),
          ),
        ],
      ),
    );
  }
}
