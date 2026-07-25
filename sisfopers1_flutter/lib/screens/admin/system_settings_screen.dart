import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/api_service.dart';

class SystemSettingsScreen extends StatefulWidget {
  const SystemSettingsScreen({super.key});

  @override
  State<SystemSettingsScreen> createState() => _SystemSettingsScreenState();
}

class _SystemSettingsScreenState extends State<SystemSettingsScreen> {
  ApiService get _api => ApiService(token: Provider.of<AuthProvider>(context, listen: false).token);
  bool _isLoading = true;
  bool _isSaving = false;
  bool _isTesting = false;

  final _formKey = GlobalKey<FormState>();
  final TextEditingController _appNameController = TextEditingController();
  final TextEditingController _waHostController = TextEditingController();
  final TextEditingController _waPortController = TextEditingController();
  final TextEditingController _waApiKeyController = TextEditingController();
  final TextEditingController _waSessionController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchSettings();
  }

  @override
  void dispose() {
    _appNameController.dispose();
    _waHostController.dispose();
    _waPortController.dispose();
    _waApiKeyController.dispose();
    _waSessionController.dispose();
    super.dispose();
  }

  Future<void> _fetchSettings() async {
    setState(() {
      _isLoading = true;
    });
    try {
      final response = await _api.get('/admin/settings');
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        final settings = resData['settings'] ?? {};
        setState(() {
          _appNameController.text = settings['app_name'] ?? 'SISFOPERSKC';
          _waHostController.text = settings['wa_host'] ?? '127.0.0.1';
          _waPortController.text = settings['wa_port'] ?? '3100';
          _waApiKeyController.text = settings['wa_api_key'] ?? '';
          _waSessionController.text = settings['wa_session'] ?? 'default';
        });
      }
    } catch (_) {}
    setState(() {
      _isLoading = false;
    });
  }

  Future<void> _saveSettings() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _isSaving = true;
    });
    try {
      final response = await _api.post('/admin/settings/update', {
        'app_name': _appNameController.text,
        'wa_host': _waHostController.text,
        'wa_port': int.tryParse(_waPortController.text) ?? 3100,
        'wa_api_key': _waApiKeyController.text,
        'wa_session': _waSessionController.text,
      });
      final resData = jsonDecode(response.body);
      if (response.statusCode == 200 && resData['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(resData['message'] ?? 'Konfigurasi disimpan.'), backgroundColor: Colors.green),
        );
        _fetchSettings();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(resData['message'] ?? 'Gagal menyimpan pengaturan.'), backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Terjadi kesalahan: $e'), backgroundColor: Colors.red),
      );
    }
    setState(() {
      _isSaving = false;
    });
  }

  Future<void> _testWaConnection() async {
    setState(() {
      _isTesting = true;
    });
    try {
      final response = await _api.post('/admin/settings/test-wa', {});
      final resData = jsonDecode(response.body);
      final bool status = resData['status'] ?? false;
      final String message = resData['message'] ?? 'Tidak ada respon dari gateway.';

      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              Icon(status ? Icons.check_circle : Icons.warning, color: status ? Colors.green : Colors.orange, size: 24),
              const SizedBox(width: 8),
              Text(
                'Status WA Gateway',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 16),
              ),
            ],
          ),
          content: Text(message, style: const TextStyle(fontSize: 13, color: Color(0xff475569))),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Tutup'),
            ),
          ],
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Koneksi gagal: $e'), backgroundColor: Colors.red),
      );
    }
    setState(() {
      _isTesting = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF8FAFC),
      appBar: AppBar(
        title: Text(
          'Pengaturan Sistem',
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
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // App Config Section
                    _buildSectionHeader('Konfigurasi Aplikasi', Icons.settings),
                    const SizedBox(height: 16),
                    _buildTextField(
                      controller: _appNameController,
                      label: 'Nama Aplikasi',
                      hint: 'Misal: SISFOPERSKC',
                      validator: (val) => (val == null || val.isEmpty) ? 'Nama aplikasi wajib diisi' : null,
                    ),
                    const SizedBox(height: 24),

                    // WA Gateway Section
                    _buildSectionHeader('WhatsApp Gateway API (Node.js)', Icons.sms),
                    const SizedBox(height: 16),
                    _buildTextField(
                      controller: _waHostController,
                      label: 'IP Host / Domain',
                      hint: 'Misal: 127.0.0.1 atau domain.com',
                      validator: (val) => (val == null || val.isEmpty) ? 'Host wajib diisi' : null,
                    ),
                    const SizedBox(height: 14),
                    _buildTextField(
                      controller: _waPortController,
                      label: 'Port Koneksi',
                      hint: 'Misal: 3100',
                      keyboardType: TextInputType.number,
                      validator: (val) => (val == null || val.isEmpty) ? 'Port wajib diisi' : null,
                    ),
                    const SizedBox(height: 14),
                    _buildTextField(
                      controller: _waSessionController,
                      label: 'Nama Sesi WhatsApp',
                      hint: 'Misal: default',
                      validator: (val) => (val == null || val.isEmpty) ? 'Sesi wajib diisi' : null,
                    ),
                    const SizedBox(height: 14),
                    _buildTextField(
                      controller: _waApiKeyController,
                      label: 'API Key Token (Opsional)',
                      hint: 'Kosongkan jika tidak menggunakan token otorisasi',
                      obscureText: true,
                    ),
                    const SizedBox(height: 28),

                    // Test WA Connection Button
                    OutlinedButton.icon(
                      onPressed: _isTesting ? null : _testWaConnection,
                      icon: _isTesting
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                          : const Icon(Icons.swap_calls),
                      label: const Text('Uji Sambungan WA Gateway'),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Save Button
                    ElevatedButton(
                      onPressed: _isSaving ? null : _saveSettings,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xff1E3A8A),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: _isSaving
                          ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                          : const Text('Simpan Konfigurasi', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Row(
      children: [
        Icon(icon, color: const Color(0xff1E3A8A), size: 20),
        const SizedBox(width: 8),
        Text(
          title,
          style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
        ),
      ],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required String hint,
    TextInputType keyboardType = TextInputType.text,
    bool obscureText = false,
    String? Function(String?)? validator,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xff475569))),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          obscureText: obscureText,
          validator: validator,
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(fontSize: 12, color: Color(0xff94A3B8)),
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            filled: true,
            fillColor: Colors.white,
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xffE2E8F0)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xff1E3A8A)),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Colors.red),
            ),
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Colors.red),
            ),
          ),
        ),
      ],
    );
  }
}
