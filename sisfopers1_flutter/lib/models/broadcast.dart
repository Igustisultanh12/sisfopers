class BroadcastModel {
  final int id;
  final String uuid;
  final String title;
  final String description;
  final String matra;
  final String? eventDate;
  final String createdAt;
  final BroadcastResponseModel? response;

  BroadcastModel({
    required this.id,
    required this.uuid,
    required this.title,
    required this.description,
    required this.matra,
    this.eventDate,
    required this.createdAt,
    this.response,
  });

  factory BroadcastModel.fromJson(Map<String, dynamic> json) {
    return BroadcastModel(
      id: json['id'],
      uuid: json['uuid'] ?? '',
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      matra: json['matra'] ?? 'ALL',
      eventDate: json['event_date'],
      createdAt: json['created_at'] ?? '',
      response: json['response'] != null 
          ? BroadcastResponseModel.fromJson(json['response'])
          : null,
    );
  }
}

class BroadcastResponseModel {
  final String status;
  final String? notes;
  final String permitLetter;
  final String? respondedAt;

  BroadcastResponseModel({
    required this.status,
    this.notes,
    required this.permitLetter,
    this.respondedAt,
  });

  factory BroadcastResponseModel.fromJson(Map<String, dynamic> json) {
    return BroadcastResponseModel(
      status: json['status'] ?? 'TIDAK_HADIR',
      notes: json['notes'],
      permitLetter: json['permit_letter'] ?? 'TIDAK',
      respondedAt: json['responded_at'],
    );
  }
}
