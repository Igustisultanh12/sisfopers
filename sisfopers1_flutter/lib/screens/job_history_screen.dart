import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../providers/data_provider.dart';
import '../services/api_service.dart';

class JobHistoryScreen extends StatefulWidget {
  const JobHistoryScreen({super.key});

  @override
  State<JobHistoryScreen> createState() => _JobHistoryScreenState();
}

class _JobHistoryScreenState extends State<JobHistoryScreen> {
  Timer? _lockTimer;
  int _lockSecondsLeft = 0;
  String _currentActionType = 'UPDATE_JOB'; // INITIAL_FILL, UPDATE_JOB, PHK
  final _otpController = TextEditingController();

  // Region State Variables
  List<dynamic> _provinces = [];
  List<dynamic> _regencies = [];
  List<dynamic> _districts = [];

  String? _selectedProvinceCode;
  String? _selectedRegencyCode;
  String? _selectedDistrictCode;

  bool _loadingProvinces = false;
  bool _loadingRegencies = false;
  bool _loadingDistricts = false;

  String _formatDate(String? raw) {
    if (raw == null || raw.isEmpty) return '-';
    try {
      if (raw.contains('T')) {
        return raw.split('T')[0];
      }
      if (raw.length >= 10) {
        return raw.substring(0, 10);
      }
    } catch (_) {}
    return raw;
  }

  Future<void> _fetchProvinces({VoidCallback? onComplete}) async {
    if (_loadingProvinces) return;
    setState(() {
      _loadingProvinces = true;
    });
    try {
      final api = ApiService();
      final response = await api.get('/wilayah/provinces');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data is List) {
        setState(() {
          _provinces = data;
          if (_provinceController.text.isNotEmpty) {
            final match = _provinces.firstWhere(
              (p) => p['name'].toString().toUpperCase() == _provinceController.text.toUpperCase(),
              orElse: () => null,
            );
            if (match != null) {
              _selectedProvinceCode = match['code'].toString();
              _fetchRegencies(_selectedProvinceCode!, onComplete: onComplete);
            }
          }
        });
        if (onComplete != null) onComplete();
      }
    } catch (_) {}
    setState(() {
      _loadingProvinces = false;
    });
    if (onComplete != null) onComplete();
  }

  Future<void> _fetchRegencies(String provinceCode, {VoidCallback? onComplete}) async {
    setState(() {
      _loadingRegencies = true;
      _regencies = [];
      _districts = [];
    });
    try {
      final api = ApiService();
      final response = await api.get('/wilayah/regencies/$provinceCode');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data is List) {
        setState(() {
          _regencies = data;
          if (_cityController.text.isNotEmpty) {
            final match = _regencies.firstWhere(
              (r) => r['name'].toString().toUpperCase() == _cityController.text.toUpperCase(),
              orElse: () => null,
            );
            if (match != null) {
              _selectedRegencyCode = match['code'].toString();
              _fetchDistricts(_selectedRegencyCode!, onComplete: onComplete);
            }
          }
        });
        if (onComplete != null) onComplete();
      }
    } catch (_) {}
    setState(() {
      _loadingRegencies = false;
    });
    if (onComplete != null) onComplete();
  }

  Future<void> _fetchDistricts(String regencyCode, {VoidCallback? onComplete}) async {
    setState(() {
      _loadingDistricts = true;
      _districts = [];
    });
    try {
      final api = ApiService();
      final response = await api.get('/wilayah/districts/$regencyCode');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data is List) {
        setState(() {
          _districts = data;
          if (_districtController.text.isNotEmpty) {
            final match = _districts.firstWhere(
              (d) => d['name'].toString().toUpperCase() == _districtController.text.toUpperCase(),
              orElse: () => null,
            );
            if (match != null) {
              _selectedDistrictCode = match['code'].toString();
            }
          }
        });
        if (onComplete != null) onComplete();
      }
    } catch (_) {}
    setState(() {
      _loadingDistricts = false;
    });
    if (onComplete != null) onComplete();
  }

  // Form Controllers
  final _formKey = GlobalKey<FormState>();
  String _selectedJobType = 'SWASTA'; // ASN, SWASTA, WIRASWASTA, PELAJAR
  final _companyController = TextEditingController();
  final _positionController = TextEditingController();
  final _employeeNumController = TextEditingController();
  final _nipController = TextEditingController();
  final _startDateController = TextEditingController();
  final _provinceController = TextEditingController();
  final _cityController = TextEditingController();
  final _districtController = TextEditingController();
  final _addressController = TextEditingController();
  final _postalCodeController = TextEditingController();
  final _phkDateController = TextEditingController();
  final _phkReasonController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<DataProvider>(context, listen: false).fetchJobHistories();
    });
  }

  @override
  void dispose() {
    _lockTimer?.cancel();
    _otpController.dispose();
    _companyController.dispose();
    _positionController.dispose();
    _employeeNumController.dispose();
    _nipController.dispose();
    _startDateController.dispose();
    _provinceController.dispose();
    _cityController.dispose();
    _districtController.dispose();
    _addressController.dispose();
    _postalCodeController.dispose();
    _phkDateController.dispose();
    _phkReasonController.dispose();
    super.dispose();
  }

  void _startCountdown(int seconds) {
    _lockTimer?.cancel();
    setState(() {
      _lockSecondsLeft = seconds;
    });

    _lockTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_lockSecondsLeft > 0) {
        setState(() {
          _lockSecondsLeft--;
        });
      } else {
        _lockTimer?.cancel();
      }
    });
  }

  String _formatTimerText() {
    final minutes = (_lockSecondsLeft ~/ 60).toString().padLeft(2, '0');
    final seconds = (_lockSecondsLeft % 60).toString().padLeft(2, '0');
    return '$minutes:$seconds';
  }

  void _handleRequestOtp(String actionType) async {
    final data = Provider.of<DataProvider>(context, listen: false);
    final result = await data.requestJobOtp(actionType);

    if (mounted) {
      if (result['locked'] == true) {
        _startCountdown(result['seconds_left'] ?? 300);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Akses Anda diblokir sementara.'),
            backgroundColor: const Color(0xffEF4444),
          ),
        );
      } else if (result['success']) {
        setState(() {
          _currentActionType = actionType;
        });
        _showOtpDialog();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Gagal meminta OTP.'),
            backgroundColor: const Color(0xffEF4444),
          ),
        );
      }
    }
  }

  void _showOtpDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xff25D366).withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.sms_outlined, color: Color(0xff25D366), size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Verifikasi OTP',
                      style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 18),
                    ),
                  ),
                ],
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Kami telah mengirimkan 6 digit kode verifikasi OTP ke WhatsApp Anda. Silakan periksa pesan masuk.',
                    style: TextStyle(fontSize: 13, color: Color(0xff64748B), height: 1.4),
                  ),
                  const SizedBox(height: 20),
                  TextField(
                    controller: _otpController,
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    textAlign: TextAlign.center,
                    style: GoogleFonts.outfit(fontSize: 20, fontWeight: FontWeight.bold, letterSpacing: 6),
                    decoration: InputDecoration(
                      hintText: '000000',
                      hintStyle: TextStyle(color: Colors.grey[300], letterSpacing: 6),
                      counterText: '',
                      filled: true,
                      fillColor: const Color(0xffF1F5F9),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: const BorderSide(color: Color(0xffE2E8F0)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: const BorderSide(color: Color(0xff1E3A8A), width: 1.5),
                      ),
                    ),
                  ),
                  if (_lockSecondsLeft > 0)
                    Padding(
                      padding: const EdgeInsets.only(top: 14),
                      child: Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xffFEF2F2),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xffFEE2E2)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.lock_clock, color: Color(0xffEF4444), size: 16),
                            const SizedBox(width: 8),
                            Text(
                              'Terblokir: ${_formatTimerText()}',
                              style: const TextStyle(
                                color: Color(0xffEF4444),
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    _otpController.clear();
                    Navigator.pop(context);
                  },
                  child: Text(
                    'Batal',
                    style: GoogleFonts.outfit(color: const Color(0xff64748B), fontWeight: FontWeight.bold),
                  ),
                ),
                TextButton(
                  onPressed: _lockSecondsLeft > 0
                      ? null
                      : () async {
                          final data = Provider.of<DataProvider>(context, listen: false);
                          final result = await data.verifyJobOtp(_otpController.text, _currentActionType);

                          if (result['locked'] == true) {
                            _startCountdown(result['seconds_left'] ?? 300);
                            setDialogState(() {});
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(result['message']),
                                backgroundColor: const Color(0xffEF4444),
                              ),
                            );
                          } else if (result['success']) {
                            _otpController.clear();
                            Navigator.pop(context);
                            _showUpdateForm();
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(result['message']),
                                backgroundColor: const Color(0xffEF4444),
                              ),
                            );
                          }
                        },
                  child: Text(
                    'Verifikasi',
                    style: GoogleFonts.outfit(
                      color: _lockSecondsLeft > 0 ? const Color(0xff94A3B8) : const Color(0xff1E3A8A),
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _showUpdateForm() {
    final data = Provider.of<DataProvider>(context, listen: false);
    if (data.jobHistories.isNotEmpty) {
      final current = data.jobHistories.firstWhere((job) => job.isCurrent, orElse: () => data.jobHistories.first);
      _companyController.text = current.namaPerusahaan;
      _positionController.text = current.jabatan ?? '';
      _nipController.text = current.nip ?? '';
      _startDateController.text = _formatDate(current.tmtMulai);
      _provinceController.text = current.provinsi;
      _cityController.text = current.kabupaten;
      _districtController.text = current.kecamatan;
      _addressController.text = current.alamatLengkap;
      _postalCodeController.text = current.kodePos ?? '';
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(28),
                  topRight: Radius.circular(28),
                ),
              ),
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
                top: 24,
                left: 24,
                right: 24,
              ),
              child: SingleChildScrollView(
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    mainAxisSize: MainAxisSize.min,
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
                      const SizedBox(height: 20),
                      Text(
                        _currentActionType == 'PHK' ? 'Laporkan PHK / Pengangguran' : 'Pembaruan Data Pekerjaan',
                        style: GoogleFonts.outfit(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xff0F172A),
                        ),
                      ),
                      const SizedBox(height: 20),

                      if (_currentActionType != 'PHK') ...[
                        DropdownButtonFormField<String>(
                          value: _selectedJobType,
                          decoration: InputDecoration(
                            labelText: 'Tipe Pekerjaan',
                            labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                            filled: true,
                            fillColor: const Color(0xffF1F5F9),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                          ),
                          items: const [
                            DropdownMenuItem(value: 'ASN', child: Text('ASN (PNS / PPPK / TNI / POLRI)')),
                            DropdownMenuItem(value: 'SWASTA', child: Text('Karyawan Swasta / BUMN')),
                            DropdownMenuItem(value: 'WIRASWASTA', child: Text('Wiraswasta / Pengusaha')),
                            DropdownMenuItem(value: 'PELAJAR', child: Text('Pelajar / Mahasiswa')),
                          ],
                          onChanged: (val) {
                            setModalState(() {
                              _selectedJobType = val!;
                            });
                          },
                        ),
                        const SizedBox(height: 14),
                        _buildFormTextField(
                          controller: _companyController,
                          label: 'Nama Instansi / Perusahaan',
                          validator: (val) => val == null || val.isEmpty ? 'Harap isi nama instansi' : null,
                        ),
                        const SizedBox(height: 14),
                        _buildFormTextField(
                          controller: _positionController,
                          label: 'Jabatan / Posisi Kerja',
                        ),
                        if (_selectedJobType == 'ASN') ...[
                          const SizedBox(height: 14),
                          _buildFormTextField(
                            controller: _nipController,
                            label: 'Nomor Induk Pegawai (NIP/NRP)',
                            validator: (val) => val == null || val.isEmpty ? 'NIP wajib diisi untuk ASN' : null,
                          ),
                        ],
                        const SizedBox(height: 14),
                        _buildFormTextField(
                          controller: _startDateController,
                          label: 'TMT Mulai Kerja (Format: YYYY-MM-DD)',
                          validator: (val) => val == null || val.isEmpty ? 'Harap isi TMT mulai' : null,
                        ),
                      ] else ...[
                        _buildFormTextField(
                          controller: _phkDateController,
                          label: 'Tanggal TMT PHK (Format: YYYY-MM-DD)',
                          validator: (val) => val == null || val.isEmpty ? 'Harap isi TMT PHK' : null,
                        ),
                        const SizedBox(height: 14),
                        _buildFormTextField(
                          controller: _phkReasonController,
                          label: 'Alasan Pemutusan Hubungan Kerja (PHK)',
                          maxLines: 2,
                          validator: (val) => val == null || val.isEmpty ? 'Harap isi alasan PHK' : null,
                        ),
                      ],
                      const SizedBox(height: 14),

                      if (_currentActionType != 'PHK') ...[
                        // Inisialisasi pemuatan wilayah secara otomatis
                        if (_provinces.isEmpty && !_loadingProvinces) ...[
                          Builder(builder: (context) {
                            WidgetsBinding.instance.addPostFrameCallback((_) {
                              _fetchProvinces(onComplete: () {
                                if (mounted) setModalState(() {});
                              });
                            });
                            return const SizedBox.shrink();
                          }),
                        ],

                        // Dropdown Provinsi
                        DropdownButtonFormField<String>(
                          value: _selectedProvinceCode,
                          isExpanded: true,
                          decoration: InputDecoration(
                            labelText: _loadingProvinces ? 'Memuat Provinsi...' : 'Provinsi Lokasi Kantor',
                            labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                            filled: true,
                            fillColor: const Color(0xffF1F5F9),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                          ),
                          items: _provinces.map<DropdownMenuItem<String>>((prov) {
                            return DropdownMenuItem<String>(
                              value: prov['code'].toString(),
                              child: Text(prov['name'].toString().toUpperCase(), style: const TextStyle(fontSize: 13)),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) {
                              final name = _provinces.firstWhere((p) => p['code'].toString() == val)['name'];
                              setModalState(() {
                                _selectedProvinceCode = val;
                                _provinceController.text = name.toString().toUpperCase();
                                _selectedRegencyCode = null;
                                _selectedDistrictCode = null;
                                _cityController.text = '';
                                _districtController.text = '';
                                _regencies = [];
                                _districts = [];
                              });
                              _fetchRegencies(val, onComplete: () {
                                if (mounted) setModalState(() {});
                              });
                            }
                          },
                          validator: (val) => val == null || val.isEmpty ? 'Provinsi wajib diisi' : null,
                        ),
                        const SizedBox(height: 14),

                        // Dropdown Kabupaten / Kota
                        DropdownButtonFormField<String>(
                          value: _selectedRegencyCode,
                          isExpanded: true,
                          decoration: InputDecoration(
                            labelText: _loadingRegencies ? 'Memuat Kabupaten...' : 'Kabupaten/Kota Kantor',
                            labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                            filled: true,
                            fillColor: const Color(0xffF1F5F9),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                          ),
                          items: _regencies.map<DropdownMenuItem<String>>((reg) {
                            return DropdownMenuItem<String>(
                              value: reg['code'].toString(),
                              child: Text(reg['name'].toString().toUpperCase(), style: const TextStyle(fontSize: 13)),
                            );
                          }).toList(),
                          onChanged: _selectedProvinceCode == null ? null : (val) {
                            if (val != null) {
                              final name = _regencies.firstWhere((r) => r['code'].toString() == val)['name'];
                              setModalState(() {
                                _selectedRegencyCode = val;
                                _cityController.text = name.toString().toUpperCase();
                                _selectedDistrictCode = null;
                                _districtController.text = '';
                                _districts = [];
                              });
                              _fetchDistricts(val, onComplete: () {
                                if (mounted) setModalState(() {});
                              });
                            }
                          },
                          validator: (val) => val == null || val.isEmpty ? 'Kabupaten wajib diisi' : null,
                        ),
                        const SizedBox(height: 14),

                        // Dropdown Kecamatan
                        DropdownButtonFormField<String>(
                          value: _selectedDistrictCode,
                          isExpanded: true,
                          decoration: InputDecoration(
                            labelText: _loadingDistricts ? 'Memuat Kecamatan...' : 'Kecamatan Kantor',
                            labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
                            filled: true,
                            fillColor: const Color(0xffF1F5F9),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: Color(0xffE2E8F0))),
                          ),
                          items: _districts.map<DropdownMenuItem<String>>((dist) {
                            return DropdownMenuItem<String>(
                              value: dist['code'].toString(),
                              child: Text(dist['name'].toString().toUpperCase(), style: const TextStyle(fontSize: 13)),
                            );
                          }).toList(),
                          onChanged: _selectedRegencyCode == null ? null : (val) {
                            if (val != null) {
                              final name = _districts.firstWhere((d) => d['code'].toString() == val)['name'];
                              setModalState(() {
                                _selectedDistrictCode = val;
                                _districtController.text = name.toString().toUpperCase();
                              });
                            }
                          },
                          validator: (val) => val == null || val.isEmpty ? 'Kecamatan wajib diisi' : null,
                        ),
                        const SizedBox(height: 14),
                        _buildFormTextField(
                          controller: _addressController,
                          label: 'Alamat Lengkap Kantor',
                          maxLines: 2,
                          validator: (val) => val == null || val.isEmpty ? 'Alamat wajib diisi' : null,
                        ),
                        const SizedBox(height: 14),
                        _buildFormTextField(
                          controller: _postalCodeController,
                          label: 'Kode Pos Kantor',
                          validator: (val) => val == null || val.isEmpty ? 'Kode pos wajib diisi' : null,
                        ),
                      ],

                      const SizedBox(height: 28),
                      ElevatedButton(
                        onPressed: () async {
                          if (_formKey.currentState!.validate()) {
                            final data = Provider.of<DataProvider>(context, listen: false);
                            final payload = _currentActionType == 'PHK'
                                ? {
                                    'tipe_pekerjaan': 'TIDAK_BEKERJA',
                                    'nama_perusahaan': 'Tidak Bekerja / Terkena PHK',
                                    'tmt_mulai': _phkDateController.text,
                                    'tmt_phk': _phkDateController.text,
                                    'alasan_phk': _phkReasonController.text,
                                  }
                                : {
                                    'tipe_pekerjaan': _selectedJobType,
                                    'nama_perusahaan': _companyController.text,
                                    'jabatan': _positionController.text,
                                    'nomor_karyawan': _employeeNumController.text,
                                    'nip': _nipController.text,
                                    'tmt_mulai': _startDateController.text,
                                    'provinsi': _provinceController.text,
                                    'kabupaten': _cityController.text,
                                    'kecamatan': _districtController.text,
                                    'alamat_lengkap': _addressController.text,
                                    'postal_code': _postalCodeController.text,
                                  };

                            final res = await data.submitJobHistory(payload);
                            if (mounted) {
                              if (res['success']) {
                                Navigator.pop(context);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Pembaruan data sukses disimpan!', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
                                    backgroundColor: const Color(0xff10B981),
                                  ),
                                );
                              } else {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(res['message'] ?? 'Gagal menyimpan data.'),
                                    backgroundColor: const Color(0xffEF4444),
                                  ),
                                );
                              }
                            }
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xff1E3A8A),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          elevation: 0,
                        ),
                        child: Text(
                          'Simpan Data Pekerjaan',
                          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 14),
                        ),
                      ),
                      const SizedBox(height: 12),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildFormTextField({
    required TextEditingController controller,
    required String label,
    int maxLines = 1,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      style: const TextStyle(color: Color(0xff0F172A), fontSize: 14),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: Color(0xff64748B), fontSize: 13),
        filled: true,
        fillColor: const Color(0xffF1F5F9),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xffE2E8F0)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xff1E3A8A), width: 1.5),
        ),
      ),
      validator: validator,
    );
  }

  @override
  Widget build(BuildContext context) {
    final data = Provider.of<DataProvider>(context);
    final currentJob = data.jobHistories.isEmpty
        ? null
        : data.jobHistories.firstWhere((job) => job.isCurrent, orElse: () => data.jobHistories.first);

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
              'Riwayat Pekerjaan',
              style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
            ),
          ),
        ),
      ),
      body: data.isLoading && data.jobHistories.isEmpty
          ? const Center(child: CircularProgressIndicator(color: Color(0xff1E3A8A)))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Active Job Info Card
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: const Color(0xffE2E8F0)),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.02),
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
                            Text(
                              'PEKERJAAN AKTIF SAAT INI',
                              style: GoogleFonts.outfit(
                                fontSize: 10,
                                fontWeight: FontWeight.w900,
                                color: const Color(0xff64748B),
                                letterSpacing: 0.8,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: currentJob != null && currentJob.tipePekerjaan == 'TIDAK_BEKERJA'
                                    ? const Color(0xffFEE2E2)
                                    : const Color(0xffEFF6FF),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                currentJob?.tipePekerjaan == 'TIDAK_BEKERJA' ? 'TIDAK BEKERJA' : (currentJob?.tipePekerjaan ?? 'TIDAK BEKERJA'),
                                style: GoogleFonts.outfit(
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                  color: currentJob != null && currentJob.tipePekerjaan == 'TIDAK_BEKERJA'
                                      ? const Color(0xffEF4444)
                                      : const Color(0xff1D4ED8),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          currentJob?.namaPerusahaan ?? 'Belum Mengisi Data Pekerjaan',
                          style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold, color: const Color(0xff0F172A)),
                        ),
                        if (currentJob?.jabatan != null && currentJob!.jabatan!.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            currentJob.jabatan!,
                            style: const TextStyle(fontSize: 13, color: Color(0xff64748B)),
                          ),
                        ],
                        if (currentJob?.nip != null && currentJob!.nip!.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xffF1F5F9),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'NIP/NRP: ${currentJob.nip}',
                              style: const TextStyle(fontSize: 11, color: Color(0xff475569), fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                        const SizedBox(height: 16),
                        const Divider(color: Color(0xffE2E8F0)),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            const Icon(Icons.location_on_outlined, size: 16, color: Color(0xff94A3B8)),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                currentJob != null
                                    ? '${currentJob.alamatLengkap}, ${currentJob.kecamatan}, ${currentJob.kabupaten}, ${currentJob.provinsi}'
                                    : '-',
                                style: const TextStyle(fontSize: 12, color: Color(0xff64748B)),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(Icons.calendar_today_outlined, size: 15, color: Color(0xff94A3B8)),
                            const SizedBox(width: 8),
                            Text(
                              currentJob != null ? 'Terhitung Mulai Tanggal: ${_formatDate(currentJob.tmtMulai)}' : '-',
                              style: const TextStyle(fontSize: 12, color: Color(0xff64748B)),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Actions Row (Modern Buttons)
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          height: 48,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(16),
                            gradient: const LinearGradient(
                              colors: [Color(0xff1E3A8A), Color(0xff1D4ED8)],
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xff1E3A8A).withOpacity(0.15),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: ElevatedButton(
                            onPressed: _lockSecondsLeft > 0
                                ? null
                                : () => _handleRequestOtp('UPDATE_JOB'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.transparent,
                              shadowColor: Colors.transparent,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            ),
                            child: Text(
                              _lockSecondsLeft > 0 ? _formatTimerText() : 'Pembaruan Data',
                              style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Container(
                          height: 48,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(16),
                            color: const Color(0xffEF4444),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xffEF4444).withOpacity(0.15),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: ElevatedButton(
                            onPressed: _lockSecondsLeft > 0
                                ? null
                                : () => _handleRequestOtp('PHK'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.transparent,
                              shadowColor: Colors.transparent,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            ),
                            child: Text(
                              _lockSecondsLeft > 0 ? _formatTimerText() : 'Laporkan PHK',
                              style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 36),

                  // Job History Timeline
                  Text(
                    'Riwayat Transisi Pekerjaan',
                    style: GoogleFonts.outfit(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xff0F172A),
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 14),
                  data.jobHistories.isEmpty
                      ? Container(
                          padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xffE2E8F0)),
                          ),
                          child: const Column(
                            children: [
                              Icon(Icons.history, color: Color(0xff94A3B8), size: 36),
                              SizedBox(height: 10),
                              Text(
                                'Belum ada riwayat transisi pekerjaan terdokumentasi.',
                                style: TextStyle(color: Color(0xff94A3B8), fontSize: 12, fontStyle: FontStyle.italic),
                                textAlign: TextAlign.center,
                              ),
                            ],
                          ),
                        )
                      : ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: data.jobHistories.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 12),
                          itemBuilder: (context, index) {
                            final item = data.jobHistories[index];
                            return Container(
                              padding: const EdgeInsets.all(18),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: const Color(0xffE2E8F0)),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.01),
                                    blurRadius: 8,
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
                                          color: item.isPhk
                                              ? const Color(0xffFEE2E2)
                                              : const Color(0xffEFF6FF),
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          item.tipePekerjaan,
                                          style: GoogleFonts.outfit(
                                            fontSize: 9,
                                            fontWeight: FontWeight.bold,
                                            color: item.isPhk ? const Color(0xffEF4444) : const Color(0xff1D4ED8),
                                          ),
                                        ),
                                      ),
                                      if (item.isCurrent)
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: const Color(0xffD1FAE5),
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: const Text(
                                            'AKTIF',
                                            style: TextStyle(fontSize: 8, fontWeight: FontWeight.bold, color: Color(0xff059669)),
                                          ),
                                        ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Text(
                                    item.namaPerusahaan,
                                    style: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: const Color(0xff1E293B)),
                                  ),
                                  if (item.jabatan != null && item.jabatan!.isNotEmpty) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      item.jabatan!,
                                      style: const TextStyle(fontSize: 12, color: Color(0xff64748B)),
                                    ),
                                  ],
                                  if (item.isPhk && item.alasanPhk != null && item.alasanPhk!.isNotEmpty) ...[
                                    const SizedBox(height: 10),
                                    Container(
                                      padding: const EdgeInsets.all(10),
                                      decoration: BoxDecoration(
                                        color: const Color(0xffFEF2F2),
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(color: const Color(0xffFEE2E2)),
                                      ),
                                      child: Row(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          const Icon(Icons.info, color: Color(0xffEF4444), size: 14),
                                          const SizedBox(width: 6),
                                          Expanded(
                                            child: Text(
                                              'Alasan PHK: ${item.alasanPhk}',
                                              style: const TextStyle(fontSize: 11, color: Color(0xffEF4444), height: 1.4),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            );
                          },
                        ),
                ],
              ),
            ),
    );
  }
}
