import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/api_service.dart';

class SkepVerificationScreen extends StatefulWidget {
  const SkepVerificationScreen({super.key});

  @override
  State<SkepVerificationScreen> createState() => _SkepVerificationScreenState();
}

class _SkepVerificationScreenState extends State<SkepVerificationScreen> {
  List<dynamic> _requests = [];
  bool _isLoading = false;

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
      final token = Provider.of<AuthProvider>(context, listen: false).token;
      final api = ApiService(token: token);
      final response = await api.get('/admin/skep-requests');
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

  Future<void> _openPdf(String? urlStr) async {
    if (urlStr == null || urlStr.isEmpty) return;
    final url = Uri.parse(urlStr);
    try {
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Tidak dapat membuka link berkas SKEP')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error membuka berkas: $e')),
        );
      }
    }
  }

  void _showVerifyDialog(dynamic req, String action) {
    final notesController = TextEditingController();
    final isApproved = (action == 'APPROVED');

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: Text(
            isApproved ? 'Setujui Berkas SKEP' : 'Tolak Berkas SKEP',
            style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0f172a)),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                isApproved
                    ? 'Apakah Anda yakin ingin menyetujui SKEP dari ${req['nama_lengkap']}? Data NIKC ini akan otomatis masuk ke database SKEP aktif.'
                    : 'Berikan alasan penolakan berkas SKEP agar pendaftar dapat mengetahuinya:',
                style: const TextStyle(fontSize: 12, color: Color(0xff64748B), height: 1.4),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: notesController,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: isApproved ? 'Catatan Tambahan (Opsional)' : 'Catatan Penolakan (Wajib)',
                  labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                  filled: true,
                  fillColor: const Color(0xffF8FAFC),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                ),
                style: const TextStyle(fontSize: 13),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text(
                'Batal',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff64748B)),
              ),
            ),
            ElevatedButton(
              onPressed: () async {
                if (!isApproved && notesController.text.trim().isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Catatan penolakan wajib diisi'), backgroundColor: Color(0xffEF4444)),
                  );
                  return;
                }
                Navigator.pop(context);
                await _processVerification(req['id'], action, notesController.text.trim());
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: isApproved ? const Color(0xff10B981) : const Color(0xffEF4444),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                elevation: 0,
              ),
              child: Text(
                isApproved ? 'Setujui' : 'Tolak',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold),
              ),
            ),
          ],
        );
      },
    );
  }

  Future<void> _processVerification(int id, String status, String notes) async {
    setState(() {
      _isLoading = true;
    });
    try {
      final token = Provider.of<AuthProvider>(context, listen: false).token;
      final api = ApiService(token: token);
      final response = await api.post('/admin/skep-requests/$id/verify', {
        'status': status,
        'admin_notes': notes.isEmpty ? null : notes,
      });
      final resData = jsonDecode(response.body);

      if (!mounted) return;

      if (response.statusCode == 200 && resData['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(status == 'APPROVED' ? 'Berkas SKEP disetujui!' : 'Berkas SKEP ditolak!'),
            backgroundColor: status == 'APPROVED' ? const Color(0xff10B981) : const Color(0xffEF4444),
          ),
        );
        _fetchRequests();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(resData['message'] ?? 'Gagal memproses verifikasi'), backgroundColor: const Color(0xffEF4444)),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Terjadi kesalahan: $e'), backgroundColor: const Color(0xffEF4444)),
        );
      }
    }
    setState(() {
      _isLoading = false;
    });
  }

  Color _getMatraColor(String matra) {
    switch (matra.toUpperCase()) {
      case 'AD':
        return const Color(0xff16A34A);
      case 'AL':
        return const Color(0xff1E3A8A);
      case 'AU':
        return const Color(0xff0EA5E9);
      default:
        return const Color(0xff64748B);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: AppBar(
        title: Text(
          'Verifikasi Pengajuan SKEP',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A), fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xff0F172A)),
        shape: Border(bottom: BorderSide(color: const Color(0xff0F172A).withOpacity(0.06), width: 1.5)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(Color(0xff1E3A8A))))
          : _requests.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.assignment_turned_in_outlined, size: 48, color: Colors.grey[300]),
                      const SizedBox(height: 12),
                      Text(
                        'Tidak ada pengajuan SKEP pending',
                        style: GoogleFonts.outfit(color: const Color(0xff64748B), fontSize: 13),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchRequests,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16.0),
                    itemCount: _requests.length,
                    itemBuilder: (context, index) {
                      final req = _requests[index];
                      final matra = req['matra']?.toString() ?? 'AD';
                      return Card(
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                          side: const BorderSide(color: Color(0xffE2E8F0)),
                        ),
                        margin: const EdgeInsets.only(bottom: 16.0),
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    req['nama_lengkap'] ?? '-',
                                    style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 15, color: const Color(0xff0f172a)),
                                  ),
                                  Container(
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
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text('NIKC: ${req['nikc'] ?? '-'}', style: const TextStyle(fontSize: 12, color: Color(0xff64748B))),
                              Text('Pangkat: ${req['pangkat'] ?? '-'}', style: const TextStyle(fontSize: 12, color: Color(0xff64748B))),
                              Text('Angkatan: ${req['angkatan'] ?? '-'}', style: const TextStyle(fontSize: 12, color: Color(0xff64748B))),
                              Text('No. WhatsApp: ${req['phone_number'] ?? '-'}', style: const TextStyle(fontSize: 12, color: Color(0xff64748B))),
                              const SizedBox(height: 14),
                              
                              // Preview PDF Button
                              OutlinedButton.icon(
                                onPressed: () => _openPdf(req['ktp_document']),
                                icon: const Icon(Icons.picture_as_pdf, size: 16),
                                label: const Text('Pratinjau Berkas KTP/SKEP'),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xff1E3A8A),
                                  side: const BorderSide(color: Color(0xff1E3A8A)),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                                ),
                              ),
                              const SizedBox(height: 14),
                              
                              // Action Buttons
                              Row(
                                children: [
                                  Expanded(
                                    child: ElevatedButton(
                                      onPressed: () => _showVerifyDialog(req, 'APPROVED'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xff10B981),
                                        foregroundColor: Colors.white,
                                        elevation: 0,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                      ),
                                      child: Text(
                                        'Setujui',
                                        style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: ElevatedButton(
                                      onPressed: () => _showVerifyDialog(req, 'REJECTED'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xffEF4444),
                                        foregroundColor: Colors.white,
                                        elevation: 0,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                      ),
                                      child: Text(
                                        'Tolak',
                                        style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
