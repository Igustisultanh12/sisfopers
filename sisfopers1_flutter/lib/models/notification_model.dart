class NotificationModel {
  final String id;
  final String title;
  final String message;
  final String icon;
  final String? url;
  final String? readAt;
  final String createdAt;

  NotificationModel({
    required this.id,
    required this.title,
    required this.message,
    required this.icon,
    this.url,
    this.readAt,
    required this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'] ?? '',
      title: json['title'] ?? 'Pemberitahuan',
      message: json['message'] ?? '',
      icon: json['icon'] ?? 'bell',
      url: json['url'],
      readAt: json['read_at'],
      createdAt: json['created_at'] ?? '',
    );
  }
}
