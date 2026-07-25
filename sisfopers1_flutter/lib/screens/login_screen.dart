import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _obscureText = true;
  bool _rememberMe = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<AuthProvider>(context, listen: false).fetchPublicSettings();
    });
  }

  void _handleLogin() async {
    if (_formKey.currentState!.validate()) {
      final auth = Provider.of<AuthProvider>(context, listen: false);
      final result = await auth.login(
        _usernameController.text,
        _passwordController.text,
      );

      if (mounted) {
        if (result['success']) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                'Selamat datang kembali di SISFOPERSKC!',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold),
              ),
              backgroundColor: const Color(0xff10B981),
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          );
        } else {
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Text(
                'Login Gagal',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: const Color(0xffEF4444)),
              ),
              content: Text(result['message'] ?? 'Periksa kembali kredensial Anda.'),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text('Coba Lagi', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final size = MediaQuery.of(context).size;

    return Scaffold(
      body: Container(
        height: size.height,
        width: size.width,
        decoration: BoxDecoration(
          image: auth.publicSettings?['login_background'] != null
              ? DecorationImage(
                  image: NetworkImage(auth.publicSettings!['login_background']),
                  fit: BoxFit.cover,
                  colorFilter: ColorFilter.mode(
                    Colors.black.withOpacity(0.82),
                    BlendMode.srcOver,
                  ),
                )
              : null,
          gradient: auth.publicSettings?['login_background'] == null
              ? const LinearGradient(
                  colors: [Color(0xff0B132B), Color(0xff1C2541)],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                )
              : null,
        ),
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 24.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 10),
                  
                  // Top Small Header (SF SISFOPERSKC)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xff3B82F6),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text(
                          'SF',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        auth.publicSettings?['app_name'] ?? 'SISFOPERSKC',
                        style: GoogleFonts.outfit(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 32),

                  // Large Pentagonal TNI Logo
                  Center(
                    child: Container(
                      height: 120,
                      width: 120,
                      decoration: BoxDecoration(
                        image: auth.publicSettings?['logo_tni'] != null
                            ? DecorationImage(
                                image: NetworkImage(auth.publicSettings!['logo_tni']),
                                fit: BoxFit.contain,
                              )
                            : const DecorationImage(
                                image: AssetImage('assets/icon/app_icon.png'),
                                fit: BoxFit.contain,
                              ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Integrasi TNI text & Subtitle
                  Text(
                    '${auth.publicSettings?['app_name'] ?? 'SISFOPERSKC'} INTEGRASI TNI',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.outfit(
                      color: Colors.white,
                      fontWeight: FontWeight.w900,
                      fontSize: 18,
                      letterSpacing: 0.8,
                    ),
                  ),
                  
                  // Baris Logo Matra AD, AL, AU di bawah logo utama jika disetting di admin
                  if (auth.publicSettings?['logo_ad'] != null ||
                      auth.publicSettings?['logo_al'] != null ||
                      auth.publicSettings?['logo_au'] != null) ...[
                    const SizedBox(height: 14),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (auth.publicSettings?['logo_ad'] != null) ...[
                          Image.network(auth.publicSettings!['logo_ad'], height: 26, fit: BoxFit.contain),
                          const SizedBox(width: 12),
                        ],
                        if (auth.publicSettings?['logo_al'] != null) ...[
                          Image.network(auth.publicSettings!['logo_al'], height: 26, fit: BoxFit.contain),
                          const SizedBox(width: 12),
                        ],
                        if (auth.publicSettings?['logo_au'] != null) ...[
                          Image.network(auth.publicSettings!['logo_au'], height: 26, fit: BoxFit.contain),
                        ],
                      ],
                    ),
                  ],
                  const SizedBox(height: 8),
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.0),
                    child: Text(
                      'Sistem informasi personel yang terintegrasi, valid, dan akuntabel untuk pengelolaan administrasi personel komponen cadangan.',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Color(0xff8892B0),
                        fontSize: 11,
                        height: 1.4,
                      ),
                    ),
                  ),
                  const SizedBox(height: 36),

                  // Form Container (CoreTax Web Style Translucent Card)
                  Container(
                    decoration: BoxDecoration(
                      color: const Color(0xff0B1E36).withOpacity(0.65),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: Colors.white.withOpacity(0.08)),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.2),
                          blurRadius: 15,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          'Masuk Akun',
                          style: GoogleFonts.outfit(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Gunakan akun internal Anda untuk mengakses sistem dashboard.',
                          style: TextStyle(
                            fontSize: 12,
                            color: Color(0xff8892B0),
                          ),
                        ),
                        const SizedBox(height: 24),

                        // USERNAME / NIKC Label
                        const Text(
                          'USERNAME / NIKC',
                          style: TextStyle(
                            color: Color(0xff8892B0),
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.5,
                          ),
                        ),
                        const SizedBox(height: 6),
                        TextFormField(
                          controller: _usernameController,
                          style: const TextStyle(color: Colors.white, fontSize: 14),
                          decoration: InputDecoration(
                            hintText: 'Masukkan username atau NIKC',
                            hintStyle: const TextStyle(color: Color(0xff495670), fontSize: 13),
                            filled: true,
                            fillColor: const Color(0xff1E293B),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xff3B82F6), width: 1.5),
                            ),
                            errorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xffEF4444)),
                            ),
                          ),
                          validator: (value) => value == null || value.isEmpty ? 'Harap isi username' : null,
                        ),
                        const SizedBox(height: 18),

                        // KATA KUNCI Label & Lupa Password
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'KATA KUNCI',
                              style: TextStyle(
                                color: Color(0xff8892B0),
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 0.5,
                              ),
                            ),
                            Text(
                              'Lupa Password?',
                              style: GoogleFonts.outfit(
                                color: const Color(0xffF97316),
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        TextFormField(
                          controller: _passwordController,
                          obscureText: _obscureText,
                          style: const TextStyle(color: Colors.white, fontSize: 14),
                          decoration: InputDecoration(
                            hintText: 'Masukkan kata kunci',
                            hintStyle: const TextStyle(color: Color(0xff495670), fontSize: 13),
                            suffixIcon: IconButton(
                              icon: Icon(
                                _obscureText ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                color: const Color(0xff8892B0),
                                size: 18,
                              ),
                              onPressed: () => setState(() => _obscureText = !_obscureText),
                            ),
                            filled: true,
                            fillColor: const Color(0xff1E293B),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xff3B82F6), width: 1.5),
                            ),
                            errorBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xffEF4444)),
                            ),
                          ),
                          validator: (value) => value == null || value.isEmpty ? 'Harap isi password' : null,
                        ),
                        const SizedBox(height: 14),

                        // Checkbox Ingat Saya
                        Row(
                          children: [
                            SizedBox(
                              width: 24,
                              height: 24,
                              child: Checkbox(
                                value: _rememberMe,
                                activeColor: const Color(0xffF97316),
                                checkColor: Colors.white,
                                side: const BorderSide(color: Color(0xff495670)),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                onChanged: (val) {
                                  setState(() {
                                    _rememberMe = val ?? false;
                                  });
                                },
                              ),
                            ),
                            const SizedBox(width: 8),
                            const Text(
                              'Ingat Saya',
                              style: TextStyle(
                                color: Color(0xff8892B0),
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),

                        // Orange Login Button
                        auth.isLoading
                            ? const Center(
                                child: Padding(
                                  padding: EdgeInsets.symmetric(vertical: 8.0),
                                  child: CircularProgressIndicator(color: Color(0xffF97316)),
                                ),
                              )
                            : Container(
                                height: 50,
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(12),
                                  gradient: const LinearGradient(
                                    colors: [Color(0xffEA580C), Color(0xffF97316)],
                                  ),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xffF97316).withOpacity(0.2),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: ElevatedButton(
                                  onPressed: _handleLogin,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.transparent,
                                    shadowColor: Colors.transparent,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        'Masuk Sistem',
                                        style: GoogleFonts.outfit(
                                          fontSize: 14,
                                          fontWeight: FontWeight.bold,
                                          color: Colors.white,
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      const Icon(Icons.arrow_forward, color: Colors.white, size: 16),
                                    ],
                                  ),
                                ),
                              ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Footer Register Link
                  Center(
                    child: RichText(
                      textAlign: TextAlign.center,
                      text: TextSpan(
                        style: GoogleFonts.outfit(fontSize: 12, color: const Color(0xff8892B0)),
                        children: [
                          const TextSpan(text: 'Belum memiliki akun Komponen Cadangan? '),
                          TextSpan(
                            text: 'Daftar Sekarang',
                            style: GoogleFonts.outfit(
                              color: const Color(0xffF97316),
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
