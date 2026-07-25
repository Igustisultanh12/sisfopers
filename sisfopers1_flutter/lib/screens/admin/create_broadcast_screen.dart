import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class CreateBroadcastScreen extends StatefulWidget {
  const CreateBroadcastScreen({super.key});

  @override
  State<CreateBroadcastScreen> createState() => _CreateBroadcastScreenState();
}

class _CreateBroadcastScreenState extends State<CreateBroadcastScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _locationController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _eventDateController = TextEditingController();
  final _eventTimeController = TextEditingController();
  final _deadlineController = TextEditingController();

  String _category = 'Pengumuman'; // Latihan, Apel, Mobilisasi, Pengumuman
  String _targetType = 'ALL'; // ALL, MATRA, ANGKATAN
  String _targetValue = '';

  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _initCoordinatorTargets();
  }

  void _initCoordinatorTargets() {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final role = auth.user?.role ?? 'personel';
    final p = auth.user?.personel;

    if (role == 'kordinator_matra' && p != null) {
      _targetType = 'MATRA';
      _targetValue = p.matra;
    } else if (role == 'kordinator_angkatan' && p != null) {
      _targetType = 'ANGKATAN';
      _targetValue = p.angkatan;
    }
  }

  Future<void> _selectDate(BuildContext context, TextEditingController controller) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xff1E3A8A),
              onPrimary: Colors.white,
              onSurface: Color(0xff0F172A),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        controller.text = "${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}";
      });
    }
  }

  Future<void> _selectTime(BuildContext context) async {
    final TimeOfDay? picked = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xff1E3A8A),
              onPrimary: Colors.white,
              onSurface: Color(0xff0F172A),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        _eventTimeController.text = "${picked.hour.toString().padLeft(2, '0')}:${picked.minute.toString().padLeft(2, '0')}";
      });
    }
  }

  Future<void> _selectDateTime(BuildContext context, TextEditingController controller) async {
    final DateTime? pickedDate = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
    );
    if (pickedDate != null) {
      if (!context.mounted) return;
      final TimeOfDay? pickedTime = await showTimePicker(
        context: context,
        initialTime: TimeOfDay.now(),
      );
      if (pickedTime != null) {
        setState(() {
          final dt = DateTime(pickedDate.year, pickedDate.month, pickedDate.day, pickedTime.hour, pickedTime.minute);
          controller.text = dt.toIso8601String().split('.')[0]; // Format clean ISO string
        });
      }
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSubmitting = true;
    });

    try {
      final token = Provider.of<AuthProvider>(context, listen: false).token;
    final api = ApiService(token: token);
      final payload = {
        'title': _titleController.text,
        'category': _category,
        'event_date': _eventDateController.text,
        'event_time': _eventTimeController.text,
        'location': _locationController.text,
        'description': _descriptionController.text,
        'deadline': _deadlineController.text,
        'target_type': _targetType,
        'target_value': _targetValue.isEmpty ? null : _targetValue,
      };

      final response = await api.post('/admin/broadcasts/store', payload);
      final resData = jsonDecode(response.body);

      if (!mounted) return;

      if (response.statusCode == 200 && resData['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(resData['message'] ?? 'Broadcast sukses dikirim!'), backgroundColor: const Color(0xff10B981)),
        );
        Navigator.pop(context, true);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(resData['message'] ?? 'Gagal mengirim broadcast'), backgroundColor: const Color(0xffEF4444)),
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
      _isSubmitting = false;
    });
  }

  Widget _buildFormTextField({
    required TextEditingController controller,
    required String label,
    required String? Function(String?)? validator,
    int maxLines = 1,
    bool readOnly = false,
    VoidCallback? onTap,
    IconData? suffixIcon,
  }) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      readOnly: readOnly,
      onTap: onTap,
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
        filled: true,
        fillColor: const Color(0xffF8FAFC),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xff1E3A8A), width: 1.5)),
        suffixIcon: suffixIcon != null ? Icon(suffixIcon, color: const Color(0xff64748B), size: 20) : null,
      ),
      validator: validator,
      style: const TextStyle(fontSize: 13, color: Color(0xff1E293B)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final role = auth.user?.role ?? 'personel';
    final isAdmin = (role == 'admin');

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(
          'Kirim Perintah Broadcast',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xff0F172A), fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xff0F172A)),
        shape: Border(bottom: BorderSide(color: const Color(0xff0F172A).withOpacity(0.06), width: 1.5)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              _buildFormTextField(
                controller: _titleController,
                label: 'Judul Perintah / Kegiatan',
                validator: (val) => val == null || val.isEmpty ? 'Harap isi judul broadcast' : null,
              ),
              const SizedBox(height: 16),

              DropdownButtonFormField<String>(
                value: _category,
                decoration: InputDecoration(
                  labelText: 'Kategori Kegiatan',
                  labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                  filled: true,
                  fillColor: const Color(0xffF8FAFC),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                ),
                items: const [
                  DropdownMenuItem(value: 'Latihan', child: Text('Latihan / Pemeliharaan')),
                  DropdownMenuItem(value: 'Apel', child: Text('Apel Siaga')),
                  DropdownMenuItem(value: 'Mobilisasi', child: Text('Mobilisasi Komando')),
                  DropdownMenuItem(value: 'Pengumuman', child: Text('Pengumuman Umum')),
                ],
                onChanged: (val) {
                  setState(() {
                    _category = val!;
                  });
                },
                style: const TextStyle(fontSize: 13, color: Color(0xff1E293B)),
              ),
              const SizedBox(height: 16),

              Row(
                children: [
                  Expanded(
                    child: _buildFormTextField(
                      controller: _eventDateController,
                      label: 'Tanggal Kegiatan',
                      readOnly: true,
                      onTap: () => _selectDate(context, _eventDateController),
                      suffixIcon: Icons.calendar_today,
                      validator: (val) => val == null || val.isEmpty ? 'Tanggal wajib diisi' : null,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: _buildFormTextField(
                      controller: _eventTimeController,
                      label: 'Waktu (WIB)',
                      readOnly: true,
                      onTap: () => _selectTime(context),
                      suffixIcon: Icons.access_time,
                      validator: (val) => val == null || val.isEmpty ? 'Waktu wajib diisi' : null,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              _buildFormTextField(
                controller: _locationController,
                label: 'Lokasi Penugasan',
                validator: (val) => val == null || val.isEmpty ? 'Harap isi lokasi' : null,
              ),
              const SizedBox(height: 16),

              _buildFormTextField(
                controller: _deadlineController,
                label: 'Batas Waktu Respon Kehadiran',
                readOnly: true,
                onTap: () => _selectDateTime(context, _deadlineController),
                suffixIcon: Icons.event_busy,
                validator: (val) => val == null || val.isEmpty ? 'Batas waktu respon wajib diisi' : null,
              ),
              const SizedBox(height: 16),

              // TARGET SELECTION (Only visible for Admin, Coordinator is prefilled)
              if (isAdmin) ...[
                DropdownButtonFormField<String>(
                  value: _targetType,
                  decoration: InputDecoration(
                    labelText: 'Tipe Target Penerima',
                    labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                    filled: true,
                    fillColor: const Color(0xffF8FAFC),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                  ),
                  items: const [
                    DropdownMenuItem(value: 'ALL', child: Text('Semua Personel Komcad')),
                    DropdownMenuItem(value: 'MATRA', child: Text('Matra Spesifik (AD / AL / AU)')),
                    DropdownMenuItem(value: 'ANGKATAN', child: Text('Tahun Angkatan Kelulusan')),
                  ],
                  onChanged: (val) {
                    setState(() {
                      _targetType = val!;
                      _targetValue = '';
                    });
                  },
                  style: const TextStyle(fontSize: 13, color: Color(0xff1E293B)),
                ),
                if (_targetType != 'ALL') ...[
                  const SizedBox(height: 16),
                  if (_targetType == 'MATRA')
                    DropdownButtonFormField<String>(
                      value: _targetValue.isEmpty ? null : _targetValue,
                      decoration: InputDecoration(
                        labelText: 'Pilih Matra Target',
                        labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                        filled: true,
                        fillColor: const Color(0xffF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'AD', child: Text('TNI Angkatan Darat (AD)')),
                        DropdownMenuItem(value: 'AL', child: Text('TNI Angkatan Laut (AL)')),
                        DropdownMenuItem(value: 'AU', child: Text('TNI Angkatan Udara (AU)')),
                      ],
                      onChanged: (val) {
                        setState(() {
                          _targetValue = val!;
                        });
                      },
                      validator: (val) => val == null || val.isEmpty ? 'Matra target wajib diisi' : null,
                      style: const TextStyle(fontSize: 13, color: Color(0xff1E293B)),
                    )
                  else
                    TextFormField(
                      decoration: InputDecoration(
                        labelText: 'Masukkan Tahun Angkatan (Contoh: 2024)',
                        labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                        filled: true,
                        fillColor: const Color(0xffF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                      ),
                      style: const TextStyle(fontSize: 13, color: Color(0xff1E293B)),
                      onChanged: (val) {
                        _targetValue = val;
                      },
                      validator: (val) => val == null || val.isEmpty ? 'Tahun angkatan wajib diisi' : null,
                    ),
                ],
              ] else ...[
                // Coordinator static indicator (shows premium feedback)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: const Color(0xff1E3A8A).withOpacity(0.05),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xff1E3A8A).withOpacity(0.15)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.info, color: Color(0xff1E3A8A), size: 20),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          _targetType == 'MATRA'
                              ? 'Target Otomatis: Personel TNI $_targetValue koordinasi Anda'
                              : 'Target Otomatis: Personel Angkatan $_targetValue koordinasi Anda',
                          style: GoogleFonts.outfit(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xff1E3A8A),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 16),

              _buildFormTextField(
                controller: _descriptionController,
                label: 'Instruksi Detail Perintah',
                maxLines: 4,
                validator: (val) => val == null || val.isEmpty ? 'Harap isi deskripsi broadcast' : null,
              ),
              const SizedBox(height: 28),

              ElevatedButton(
                onPressed: _isSubmitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xff1E3A8A),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  disabledBackgroundColor: const Color(0xffCBD5E1),
                  elevation: 0,
                ),
                child: _isSubmitting
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation(Colors.white)))
                    : Text(
                        'Kirim Broadcast Sekarang',
                        style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
