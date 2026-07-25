import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/api_service.dart';

class RegistrationVerificationScreen extends StatefulWidget {
  const RegistrationVerificationScreen({super.key});

  @override
  State<RegistrationVerificationScreen> createState() => _RegistrationVerificationScreenState();
}

class _RegistrationVerificationScreenState extends State<RegistrationVerificationScreen> {
  ApiService get _api => ApiService(token: Provider.of<AuthProvider>(context, listen: false).token);
  List<dynamic> _requests = [];
  bool _isLoading = true;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _fetchRequests();
  }

  Future<void> _fetchRequests() async {
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await _api.get('/admin/pending-registrations?search=$_searchQuery');
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        setState(() {
          _requests = resData['data'] ?? [];
        });
      }
    } catch (_) {}
    setState(() {
      _isLoading = false;
    });
  }

  Future<void> _processVerification(String uuid, String status, String? notes) async {
    try {
      final response = await _api.post('/admin/pending-registrations/$uuid/verify', {
        'status': status,
        'admin_notes': notes,
      });
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(resData['message'] ?? 'Verifikasi pendaftaran diproses.'),
            backgroundColor: Colors.green,
          ),
        );
        _fetchRequests();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(resData['message'] ?? 'Gagal memproses verifikasi.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _showRejectDialog(String uuid) {
    final TextEditingController notesController = TextEditingController();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text(
          'Tolak Pendaftaran',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Masukkan alasan penolakan berkas pendaftaran:',
              style: TextStyle(fontSize: 12, color: Color(0xff64748B)),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: notesController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Misal: Dokumen KTP buram / NIK salah',
                hintStyle: const TextStyle(fontSize: 12, color: Color(0xff94A3B8)),
                contentPadding: const EdgeInsets.all(12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _processVerification(uuid, 'REJECTED', notesController.text);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Tolak Berkas', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _showApproveConfirmation(String uuid, String name) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text(
          'Setujui Pendaftaran',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
        ),
        content: Text(
          'Apakah Anda yakin ingin MENYETUJUI pendaftaran atas nama $name? Akun ybs akan langsung diaktifkan.',
          style: const TextStyle(fontSize: 13, color: Color(0xff475569)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _processVerification(uuid, 'APPROVED', null);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xff10B981),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Setujui', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Future<void> _openDocument(String? url) async {
    if (url == null || url.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tautan dokumen tidak tersedia.')),
      );
      return;
    }
    final Uri uri = Uri.parse(url);
    if (await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      // Success
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal membuka dokumen.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: AppBar(
        title: Text(
          'Konfirmasi Pendaftaran',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A), fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xff0F172A)),
        shape: Border(bottom: BorderSide(color: const Color(0xff0F172A).withOpacity(0.06), width: 1.5)),
      ),
      body: Column(
        children: [
          // Search Section
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: TextField(
              onChanged: (val) {
                _searchQuery = val;
                _fetchRequests();
              },
              decoration: InputDecoration(
                hintText: 'Cari nama, NIK, atau NIKC...',
                prefixIcon: const Icon(Icons.search, color: Color(0xff94A3B8)),
                contentPadding: const EdgeInsets.symmetric(vertical: 12),
                filled: true,
                fillColor: const Color(0xffF1F5F9),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),

          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(Color(0xff1E3A8A))))
                : _requests.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.assignment_turned_in, size: 64, color: const Color(0xff94A3B8).withOpacity(0.5)),
                            const SizedBox(height: 16),
                            Text(
                              'Antrean Pendaftaran Bersih',
                              style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: const Color(0xff475569)),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Semua pengajuan pendaftaran telah diverifikasi.',
                              style: TextStyle(fontSize: 12, color: Color(0xff64748B)),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _requests.length,
                        itemBuilder: (context, index) {
                          final item = _requests[index];
                          return Container(
                            margin: const EdgeInsets.only(bottom: 16),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: const Color(0xffE2E8F0)),
                              boxShadow: [
                                BoxShadow(color: Colors.black.withOpacity(0.01), blurRadius: 6, offset: const Offset(0, 3)),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 24,
                                      backgroundColor: const Color(0xff1E3A8A).withOpacity(0.08),
                                      backgroundImage: item['photo_profile'] != null
                                          ? NetworkImage(item['photo_profile'])
                                          : null,
                                      child: item['photo_profile'] == null
                                          ? const Icon(Icons.person, color: Color(0xff1E3A8A))
                                          : null,
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            item['full_name'] ?? '-',
                                            style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            'NIKC: ${item['nikc'] ?? '-'} | NIK: ${item['nik'] ?? '-'}',
                                            style: const TextStyle(fontSize: 11, color: Color(0xff64748B)),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: const Color(0xff1E3A8A).withOpacity(0.08),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        'TNI ${item['matra'] ?? 'AD'}',
                                        style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xff1E3A8A)),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 16),
                                Divider(color: const Color(0xffE2E8F0), height: 1),
                                const SizedBox(height: 12),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Pangkat Kelulusan:', style: TextStyle(fontSize: 12, color: Color(0xff64748B))),
                                    Text(item['pangkat'] ?? 'Prada', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xff1E293B))),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Tahun Angkatan:', style: TextStyle(fontSize: 12, color: Color(0xff64748B))),
                                    Text(item['angkatan'] ?? '-', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xff1E293B))),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Nomor Telepon:', style: TextStyle(fontSize: 12, color: Color(0xff64748B))),
                                    Text(item['phone_number'] ?? '-', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xff1E293B))),
                                  ],
                                ),
                                const SizedBox(height: 16),
                                Row(
                                  children: [
                                    Expanded(
                                      child: OutlinedButton.icon(
                                        onPressed: () => _openDocument(item['ktp_document']),
                                        icon: const Icon(Icons.picture_as_pdf, size: 16),
                                        label: const Text('Pratinjau KTP', style: TextStyle(fontSize: 11)),
                                        style: OutlinedButton.styleFrom(
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: OutlinedButton.icon(
                                        onPressed: () => _openDocument(item['photo_profile']),
                                        icon: const Icon(Icons.image, size: 16),
                                        label: const Text('Lihat Foto', style: TextStyle(fontSize: 11)),
                                        style: OutlinedButton.styleFrom(
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Expanded(
                                      child: ElevatedButton(
                                        onPressed: () => _showRejectDialog(item['uuid']),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.red.shade50,
                                          elevation: 0,
                                          shape: RoundedRectangleBorder(
                                            borderRadius: BorderRadius.circular(10),
                                            side: BorderSide(color: Colors.red.shade100),
                                          ),
                                        ),
                                        child: const Text('Tolak', style: TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.bold)),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: ElevatedButton(
                                        onPressed: () => _showApproveConfirmation(item['uuid'], item['full_name'] ?? '-'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(0xff10B981),
                                          elevation: 0,
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        ),
                                        child: const Text('Setujui', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
