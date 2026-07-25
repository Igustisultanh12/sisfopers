import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../services/api_service.dart';

class ReportsScreen extends StatefulWidget {
  const ReportsScreen({super.key});

  @override
  State<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends State<ReportsScreen> {
  ApiService get _api => ApiService(token: Provider.of<AuthProvider>(context, listen: false).token);
  bool _isLoading = true;
  Map<String, dynamic> _summary = {};
  Map<String, dynamic> _urls = {};

  @override
  void initState() {
    super.initState();
    _fetchReportSummary();
  }

  Future<void> _fetchReportSummary() async {
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await _api.get('/admin/reports');
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        setState(() {
          _summary = resData['summary'] ?? {};
          _urls = resData['export_urls'] ?? {};
        });
      }
    } catch (_) {}
    setState(() {
      _isLoading = false;
    });
  }

  Future<void> _downloadFile(String? url) async {
    if (url == null || url.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tautan unduhan tidak tersedia.')),
      );
      return;
    }
    final Uri uri = Uri.parse(url);
    if (await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      // Success
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal memulai unduhan berkas laporan.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: AppBar(
        title: Text(
          'Laporan & Pelaporan',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A), fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xff0F172A)),
        shape: Border(bottom: BorderSide(color: const Color(0xff0F172A).withOpacity(0.06), width: 1.5)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(Color(0xff1E3A8A))))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Laporan Ringkasan
                  Text(
                    'Ringkasan Data Kekuatan Komcad',
                    style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                  ),
                  const SizedBox(height: 16),

                  // Total & ASN Stats Row
                  Row(
                    children: [
                      Expanded(
                        child: _buildReportStatCard(
                          'Total Personel',
                          _summary['total_personel']?.toString() ?? '0',
                          Icons.people,
                          const Color(0xff1E3A8A),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: _buildReportStatCard(
                          'Pegawai ASN',
                          _summary['total_asn']?.toString() ?? '0',
                          Icons.work,
                          const Color(0xff10B981),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Matra Grid
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: const Color(0xffE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Rincian Berdasarkan Matra',
                          style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xff1E293B)),
                        ),
                        const SizedBox(height: 16),
                        _buildMatraBar('TNI AD (Angkatan Darat)', _summary['tni_ad'] ?? 0, _summary['total_personel'] ?? 0, const Color(0xff16A34A)),
                        const SizedBox(height: 12),
                        _buildMatraBar('TNI AL (Angkatan Laut)', _summary['tni_al'] ?? 0, _summary['total_personel'] ?? 0, const Color(0xff1E3A8A)),
                        const SizedBox(height: 12),
                        _buildMatraBar('TNI AU (Angkatan Udara)', _summary['tni_au'] ?? 0, _summary['total_personel'] ?? 0, const Color(0xff0EA5E9)),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Unduh Berkas Section
                  Text(
                    'Ekspor Data Master Dokumen',
                    style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                  ),
                  const SizedBox(height: 16),

                  // Excel Card
                  _buildDownloadCard(
                    title: 'Unduh Excel Master Personel',
                    subtitle: 'Format spreadsheet (.xlsx) lengkap untuk manipulasi data.',
                    icon: Icons.table_view,
                    color: const Color(0xff16A34A),
                    onPressed: () => _downloadFile(_urls['personel_excel']),
                  ),
                  const SizedBox(height: 14),

                  // PDF Card
                  _buildDownloadCard(
                    title: 'Unduh PDF Laporan Kekuatan',
                    subtitle: 'Format dokumen cetak (.pdf) lengkap beserta lembar tanda tangan.',
                    icon: Icons.picture_as_pdf,
                    color: const Color(0xffEF4444),
                    onPressed: () => _downloadFile(_urls['personel_pdf']),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildReportStatCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xffE2E8F0)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.08),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontSize: 10, color: Color(0xff64748B))),
                const SizedBox(height: 2),
                Text(value, style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: const Color(0xff0F172A))),
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
            Text('$count personel (${(pct * 100).toStringAsFixed(1)}%)', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xff1E293B))),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: pct,
            backgroundColor: const Color(0xffF1F5F9),
            valueColor: AlwaysStoppedAnimation(color),
            minHeight: 6,
          ),
        ),
      ],
    );
  }

  Widget _buildDownloadCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required VoidCallback onPressed,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xffE2E8F0)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withOpacity(0.08),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: const TextStyle(fontSize: 10, color: Color(0xff64748B)),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          IconButton(
            onPressed: onPressed,
            icon: const Icon(Icons.download_for_offline, color: Color(0xff1E3A8A), size: 28),
          ),
        ],
      ),
    );
  }
}
