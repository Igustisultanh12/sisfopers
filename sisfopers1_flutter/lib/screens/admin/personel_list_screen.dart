import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/api_service.dart';

class PersonelListScreen extends StatefulWidget {
  const PersonelListScreen({super.key});

  @override
  State<PersonelListScreen> createState() => _PersonelListScreenState();
}

class _PersonelListScreenState extends State<PersonelListScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<dynamic> _personels = [];
  List<dynamic> _filteredPersonels = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _fetchPersonel();
  }

  Future<void> _fetchPersonel() async {
    setState(() {
      _isLoading = true;
    });
    try {
      final token = Provider.of<AuthProvider>(context, listen: false).token;
      final api = ApiService(token: token);
      final response = await api.get('/admin/personel');
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        setState(() {
          _personels = resData['data'] ?? [];
          _filteredPersonels = _personels;
        });
      }
    } catch (_) {}
    setState(() {
      _isLoading = false;
    });
  }

  void _onSearchChanged(String query) {
    setState(() {
      if (query.isEmpty) {
        _filteredPersonels = _personels;
      } else {
        _filteredPersonels = _personels.where((p) {
          final fullName = p['full_name']?.toString().toLowerCase() ?? '';
          final nik = p['nik']?.toString().toLowerCase() ?? '';
          final nikc = p['nikc']?.toString().toLowerCase() ?? '';
          final searchLower = query.toLowerCase();
          return fullName.contains(searchLower) ||
              nik.contains(searchLower) ||
              nikc.contains(searchLower);
        }).toList();
      }
    });
  }

  Color _getMatraColor(String matra) {
    switch (matra.toUpperCase()) {
      case 'AD':
        return const Color(0xff16A34A); // Hijau AD
      case 'AL':
        return const Color(0xff1E3A8A); // Biru Gelap AL
      case 'AU':
        return const Color(0xff0EA5E9); // Biru Muda AU
      default:
        return const Color(0xff64748B);
    }
  }

  void _showDetailDialog(dynamic p) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: Text(
            'Detail Personel',
            style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0f172a)),
          ),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Center(
                  child: Container(
                    width: 90,
                    height: 120,
                    decoration: BoxDecoration(
                      color: const Color(0xffF1F5F9),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xffE2E8F0)),
                      image: p['photo_profile'] != null
                          ? DecorationImage(
                              image: NetworkImage(p['photo_profile']),
                              fit: BoxFit.cover,
                            )
                          : null,
                    ),
                    child: p['photo_profile'] == null
                        ? const Icon(Icons.person, size: 40, color: Color(0xff94A3B8))
                        : null,
                  ),
                ),
                const SizedBox(height: 20),
                _buildInfoRow('Nama Lengkap', p['full_name'] ?? '-'),
                _buildInfoRow('NIKC', p['nikc'] ?? '-'),
                _buildInfoRow('NIK (KTP)', p['nik'] ?? '-'),
                _buildInfoRow('Pangkat', p['pangkat'] ?? '-'),
                _buildInfoRow('Matra', 'TNI ${p['matra'] ?? '-'}'),
                _buildInfoRow('Angkatan Kelulusan', p['angkatan'] ?? '-'),
                _buildInfoRow('Nomor WhatsApp', p['phone_number'] ?? '-'),
                _buildInfoRow('Provinsi', p['province'] ?? '-'),
                _buildInfoRow('Kabupaten/Kota', p['city'] ?? '-'),
                _buildInfoRow('Kecamatan', p['district'] ?? '-'),
                _buildInfoRow('Alamat Lengkap', p['address'] ?? '-'),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text(
                'Tutup',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff1E3A8A)),
              ),
            )
          ],
        );
      },
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xff64748B))),
          const SizedBox(height: 2),
          Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xff1E293B))),
          const Divider(height: 10, color: Color(0xffF1F5F9)),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: AppBar(
        title: Text(
          'Kekuatan Jajaran',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A), fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xff0F172A)),
        shape: Border(bottom: BorderSide(color: const Color(0xff0F172A).withOpacity(0.06), width: 1.5)),
      ),
      body: Column(
        children: [
          // Search Bar
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Container(
              height: 48,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: const Color(0xffE2E8F0)),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Row(
                children: [
                  const Icon(Icons.search, color: Color(0xff94A3B8), size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextField(
                      controller: _searchController,
                      onChanged: _onSearchChanged,
                      decoration: const InputDecoration(
                        hintText: 'Cari nama, NIK, atau NIKC...',
                        hintStyle: TextStyle(color: Color(0xff94A3B8), fontSize: 13),
                        border: InputBorder.none,
                      ),
                      style: const TextStyle(fontSize: 13, color: Color(0xff1E293B)),
                    ),
                  ),
                  if (_searchController.text.isNotEmpty)
                    IconButton(
                      icon: const Icon(Icons.clear, size: 18, color: Color(0xff64748B)),
                      onPressed: () {
                        _searchController.clear();
                        _onSearchChanged('');
                      },
                    ),
                ],
              ),
            ),
          ),
          
          // List View
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(Color(0xff1E3A8A))))
                : _filteredPersonels.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.people_outline, size: 48, color: Colors.grey[300]),
                            const SizedBox(height: 12),
                            Text(
                              'Tidak ada jajaran personel ditemukan',
                              style: GoogleFonts.outfit(color: const Color(0xff64748B), fontSize: 13),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _fetchPersonel,
                        child: ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                          itemCount: _filteredPersonels.length,
                          itemBuilder: (context, index) {
                            final p = _filteredPersonels[index];
                            final matra = p['matra']?.toString() ?? 'AD';
                            return Card(
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                                side: const BorderSide(color: Color(0xffE2E8F0)),
                              ),
                              margin: const EdgeInsets.only(bottom: 12.0),
                              child: ListTile(
                                contentPadding: const EdgeInsets.all(12),
                                leading: Container(
                                  width: 48,
                                  height: 48,
                                  decoration: BoxDecoration(
                                    color: const Color(0xffF1F5F9),
                                    shape: BoxShape.circle,
                                    image: p['photo_profile'] != null
                                        ? DecorationImage(image: NetworkImage(p['photo_profile']), fit: BoxFit.cover)
                                        : null,
                                  ),
                                  child: p['photo_profile'] == null
                                      ? const Icon(Icons.person, color: Color(0xff94A3B8))
                                      : null,
                                ),
                                title: Text(
                                  p['full_name'] ?? '-',
                                  style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 14, color: const Color(0xff0F172A)),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const SizedBox(height: 4),
                                    Text('NIKC: ${p['nikc'] ?? '-'}', style: const TextStyle(fontSize: 11, color: Color(0xff64748B))),
                                    const SizedBox(height: 2),
                                    Text('${p['city'] ?? '-'}, ${p['province'] ?? '-'}', style: const TextStyle(fontSize: 11, color: Color(0xff64748B))),
                                  ],
                                ),
                                trailing: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: _getMatraColor(matra).withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    'TNI $matra',
                                    style: TextStyle(
                                      color: _getMatraColor(matra),
                                      fontWeight: FontWeight.bold,
                                      fontSize: 9,
                                    ),
                                  ),
                                ),
                                onTap: () => _showDetailDialog(p),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
