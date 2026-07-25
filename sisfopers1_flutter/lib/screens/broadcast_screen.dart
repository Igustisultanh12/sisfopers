import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/data_provider.dart';
import '../models/broadcast.dart';

class BroadcastScreen extends StatefulWidget {
  const BroadcastScreen({super.key});

  @override
  State<BroadcastScreen> createState() => _BroadcastScreenState();
}

class _BroadcastScreenState extends State<BroadcastScreen> {
  final _notesController = TextEditingController();
  String _selectedStatus = 'HADIR'; // HADIR, IZIN, ABSEN
  bool _requestPermit = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<DataProvider>(context, listen: false).fetchBroadcasts();
    });
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  void _showResponseDialog(BroadcastModel item) {
    _notesController.clear();
    _selectedStatus = item.response?.status ?? 'HADIR';
    _requestPermit = item.response?.permitLetter == 'YA';

    showDialog(
      context: context,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              title: Text(
                'Konfirmasi Presensi',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 18, color: const Color(0xff0F172A)),
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      item.title,
                      style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 14, color: const Color(0xff334155)),
                    ),
                    const SizedBox(height: 16),
                    DropdownButtonFormField<String>(
                      value: _selectedStatus,
                      decoration: InputDecoration(
                        labelText: 'Status Kehadiran',
                        labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                        filled: true,
                        fillColor: const Color(0xffF1F5F9),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'HADIR', child: Text('SIAP HADIR')),
                        DropdownMenuItem(value: 'IZIN', child: Text('IZIN DINAS')),
                        DropdownMenuItem(value: 'ABSEN', child: Text('ABSEN / BERHALANGAN')),
                      ],
                      onChanged: (val) {
                        setDialogState(() {
                          _selectedStatus = val!;
                        });
                      },
                    ),
                    const SizedBox(height: 14),
                    TextFormField(
                      controller: _notesController,
                      maxLines: 2,
                      style: const TextStyle(color: Color(0xff0F172A), fontSize: 14),
                      decoration: InputDecoration(
                        labelText: 'Catatan / Alasan Kehadiran',
                        labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                        filled: true,
                        fillColor: const Color(0xffF1F5F9),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xff1E3A8A), width: 1.5)),
                      ),
                    ),
                    const SizedBox(height: 10),
                    SwitchListTile(
                      title: const Text('Memerlukan Surat Izin?', style: TextStyle(fontSize: 13, color: Color(0xff334155), fontWeight: FontWeight.bold)),
                      contentPadding: EdgeInsets.zero,
                      value: _requestPermit,
                      activeColor: const Color(0xff1E3A8A),
                      onChanged: (val) {
                        setDialogState(() {
                          _requestPermit = val;
                        });
                      },
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text(
                    'Batal',
                    style: GoogleFonts.outfit(color: const Color(0xff64748B), fontWeight: FontWeight.bold),
                  ),
                ),
                TextButton(
                  onPressed: () async {
                    final data = Provider.of<DataProvider>(context, listen: false);
                    final result = await data.respondToBroadcast(
                      item.uuid,
                      _selectedStatus,
                      _notesController.text,
                      _requestPermit,
                    );

                    if (mounted) {
                      Navigator.pop(context);
                      if (result['success']) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Konfirmasi presensi berhasil dikirim!', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
                            backgroundColor: const Color(0xff10B981),
                          ),
                        );
                      } else {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(result['message'] ?? 'Gagal mengirim presensi.'),
                            backgroundColor: const Color(0xffEF4444),
                          ),
                        );
                      }
                    }
                  },
                  child: Text(
                    'Kirim Respon',
                    style: GoogleFonts.outfit(color: const Color(0xff1E3A8A), fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final data = Provider.of<DataProvider>(context);

    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(60),
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
          child: AppBar(
            backgroundColor: Colors.transparent,
            elevation: 0,
            iconTheme: const IconThemeData(color: Color(0xff0F172A)),
            title: Text(
              'Mobilisasi & Presensi Dinas',
              style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
            ),
          ),
        ),
      ),
      body: data.isLoading && data.allBroadcasts.isEmpty
          ? const Center(child: CircularProgressIndicator(color: Color(0xff1E3A8A)))
          : RefreshIndicator(
              onRefresh: () => data.fetchBroadcasts(),
              child: data.allBroadcasts.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.assignment_outlined, size: 48, color: Color(0xff94A3B8)),
                          const SizedBox(height: 12),
                          Text(
                            'Belum ada perintah dinas saat ini.',
                            style: GoogleFonts.outfit(color: const Color(0xff64748B), fontSize: 14),
                          ),
                        ],
                      ),
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.all(20.0),
                      itemCount: data.allBroadcasts.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 14),
                      itemBuilder: (context, index) {
                        final item = data.allBroadcasts[index];
                        final isResponded = item.response != null;

                        return Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(24),
                            border: Border.all(color: const Color(0xffE2E8F0)),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.01),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
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
                                      color: const Color(0xffF1F5F9),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      item.matra == 'ALL' ? 'UMUM' : 'TNI ${item.matra}',
                                      style: GoogleFonts.outfit(fontSize: 8, fontWeight: FontWeight.bold, color: const Color(0xff475569)),
                                    ),
                                  ),
                                  if (isResponded)
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: item.response!.status == 'HADIR'
                                            ? const Color(0xffD1FAE5)
                                            : (item.response!.status == 'IZIN'
                                                ? const Color(0xffFEF3C7)
                                                : const Color(0xffFEE2E2)),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        item.response!.status == 'HADIR'
                                            ? 'SIAP HADIR'
                                            : (item.response!.status == 'IZIN' ? 'IZIN DINAS' : 'ABSEN'),
                                        style: GoogleFonts.outfit(
                                          fontSize: 9,
                                          fontWeight: FontWeight.bold,
                                          color: item.response!.status == 'HADIR'
                                              ? const Color(0xff059669)
                                              : (item.response!.status == 'IZIN' ? const Color(0xffD97706) : const Color(0xffEF4444)),
                                        ),
                                      ),
                                    )
                                  else
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: const Color(0xffFEF2F2),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        'BELUM RESPON',
                                        style: GoogleFonts.outfit(fontSize: 9, fontWeight: FontWeight.bold, color: const Color(0xffEF4444)),
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 14),
                              Text(
                                item.title,
                                style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                item.description,
                                style: const TextStyle(fontSize: 12, color: Color(0xff64748B), height: 1.45),
                              ),
                              const SizedBox(height: 16),
                              const Divider(color: Color(0xffE2E8F0)),
                              const SizedBox(height: 12),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Row(
                                      children: [
                                        const Icon(Icons.calendar_today_outlined, size: 14, color: Color(0xff64748B)),
                                        const SizedBox(width: 6),
                                        Expanded(
                                          child: Text(
                                            item.eventDate != null ? 'Latihan: ${item.eventDate}' : 'Tanggal: -',
                                            style: const TextStyle(fontSize: 11, color: Color(0xff64748B), fontWeight: FontWeight.bold),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Container(
                                    height: 38,
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(10),
                                      gradient: const LinearGradient(
                                        colors: [Color(0xff1E3A8A), Color(0xff1D4ED8)],
                                      ),
                                    ),
                                    child: ElevatedButton(
                                      onPressed: () => _showResponseDialog(item),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.transparent,
                                        shadowColor: Colors.transparent,
                                        padding: const EdgeInsets.symmetric(horizontal: 16),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                        elevation: 0,
                                      ),
                                      child: Text(
                                        isResponded ? 'Ubah Presensi' : 'Isi Presensi',
                                        style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                                      ),
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
    );
  }
}
