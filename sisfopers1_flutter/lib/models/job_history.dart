class JobHistoryModel {
  final int id;
  final String tipePekerjaan;
  final String namaPerusahaan;
  final String? jabatan;
  final String? nomorKaryawan;
  final String? nip;
  final String tmtMulai;
  final String provinsi;
  final String kabupaten;
  final String kecamatan;
  final String alamatLengkap;
  final String? kodePos;
  final bool isCurrent;
  final bool isPhk;
  final String? tmtPhk;
  final String? alasanPhk;
  final String createdAt;

  JobHistoryModel({
    required this.id,
    required this.tipePekerjaan,
    required this.namaPerusahaan,
    this.jabatan,
    this.nomorKaryawan,
    this.nip,
    required this.tmtMulai,
    required this.provinsi,
    required this.kabupaten,
    required this.kecamatan,
    required this.alamatLengkap,
    this.kodePos,
    required this.isCurrent,
    required this.isPhk,
    this.tmtPhk,
    this.alasanPhk,
    required this.createdAt,
  });

  factory JobHistoryModel.fromJson(Map<String, dynamic> json) {
    return JobHistoryModel(
      id: json['id'],
      tipePekerjaan: json['tipe_pekerjaan'] ?? 'SWASTA',
      namaPerusahaan: json['nama_perusahaan'] ?? '',
      jabatan: json['jabatan'],
      nomorKaryawan: json['nomor_karyawan'],
      nip: json['nip'],
      tmtMulai: json['tmt_mulai'] ?? '',
      provinsi: json['provinsi'] ?? '',
      kabupaten: json['kabupaten'] ?? '',
      kecamatan: json['kecamatan'] ?? '',
      alamatLengkap: json['alamat_lengkap'] ?? '',
      kodePos: json['kode_pos'],
      isCurrent: json['is_current'] == 1 || json['is_current'] == true,
      isPhk: json['is_phk'] == 1 || json['is_phk'] == true,
      tmtPhk: json['tmt_phk'],
      alasanPhk: json['alasan_phk'],
      createdAt: json['created_at'] ?? '',
    );
  }
}
