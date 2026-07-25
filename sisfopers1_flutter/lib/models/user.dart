class UserModel {
  final int id;
  final String username;
  final String email;
  final String role;
  final PersonelModel? personel;

  UserModel({
    required this.id,
    required this.username,
    required this.email,
    required this.role,
    this.personel,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      username: json['username'],
      email: json['email'],
      role: json['role'] ?? 'personel',
      personel: json['personel'] != null 
          ? PersonelModel.fromJson(json['personel']) 
          : null,
    );
  }
}

class PersonelModel {
  final int id;
  final String fullName;
  final String? nikc;
  final String? pangkat;
  final String matra;
  final String angkatan;
  final bool isAsn;
  final String? phoneNumber;
  final String? address;
  final String? province;
  final String? city;
  final String? district;
  final String? village;
  final String? postalCode;
  final String? photoProfile;

  PersonelModel({
    required this.id,
    required this.fullName,
    this.nikc,
    this.pangkat,
    required this.matra,
    required this.angkatan,
    required this.isAsn,
    this.phoneNumber,
    this.address,
    this.province,
    this.city,
    this.district,
    this.village,
    this.postalCode,
    this.photoProfile,
  });

  factory PersonelModel.fromJson(Map<String, dynamic> json) {
    return PersonelModel(
      id: json['id'],
      fullName: json['full_name'],
      nikc: json['nikc'],
      pangkat: json['pangkat'],
      matra: json['matra'] ?? 'AD',
      angkatan: json['angkatan'] ?? '',
      isAsn: json['is_asn'] == 1 || json['is_asn'] == true,
      phoneNumber: json['phone_number'],
      address: json['address'],
      province: json['province'],
      city: json['city'],
      district: json['district'],
      village: json['village'],
      postalCode: json['postal_code'],
      photoProfile: json['photo_profile'],
    );
  }
}
