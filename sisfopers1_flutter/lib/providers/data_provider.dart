import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/job_history.dart';
import '../models/broadcast.dart';
import '../models/notification_model.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/notification_service.dart';

class DataProvider extends ChangeNotifier {
  final String? _authToken;
  
  Map<String, dynamic> _stats = {
    'total_kegiatan': 0,
    'total_hadir': 0,
    'total_izin': 0,
  };
  List<BroadcastModel> _latestBroadcasts = [];
  List<BroadcastModel> _allBroadcasts = [];
  List<JobHistoryModel> _jobHistories = [];
  List<NotificationModel> _notifications = [];
  int _unreadNotificationsCount = 0;
  bool _isLoading = false;

  DataProvider(this._authToken);

  Map<String, dynamic> get stats => _stats;
  List<BroadcastModel> get latestBroadcasts => _latestBroadcasts;
  List<BroadcastModel> get allBroadcasts => _allBroadcasts;
  List<JobHistoryModel> get jobHistories => _jobHistories;
  List<NotificationModel> get notifications => _notifications;
  int get unreadNotificationsCount => _unreadNotificationsCount;
  bool get isLoading => _isLoading;

  ApiService get _api => ApiService(token: _authToken);

  Future<void> fetchDashboard() async {
    _isLoading = true;
    try {
      final response = await _api.get('/dashboard');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        _stats = data['stats'];
        final List list = data['latest_broadcasts'];
        _latestBroadcasts = list.map((item) => BroadcastModel.fromJson(item)).toList();
      }
    } catch (_) {}
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchJobHistories() async {
    _isLoading = true;
    try {
      final response = await _api.get('/job-history');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        final List list = data['histories'];
        _jobHistories = list.map((item) => JobHistoryModel.fromJson(item)).toList();
      }
    } catch (_) {}
    _isLoading = false;
    notifyListeners();
  }

  Future<Map<String, dynamic>> requestJobOtp(String actionType) async {
    try {
      final response = await _api.post('/job-history/request-otp', {
        'action_type': actionType,
      });
      return jsonDecode(response.body);
    } catch (_) {
      return {'success': false, 'message': 'Koneksi ke server gagal.'};
    }
  }

  Future<Map<String, dynamic>> verifyJobOtp(String otpCode, String actionType) async {
    try {
      final response = await _api.post('/job-history/verify-otp', {
        'otp_code': otpCode,
        'action_type': actionType,
      });
      return jsonDecode(response.body);
    } catch (_) {
      return {'success': false, 'message': 'Koneksi ke server gagal.'};
    }
  }

  Future<Map<String, dynamic>> submitJobHistory(Map<String, dynamic> payload) async {
    try {
      final response = await _api.post('/job-history/store', payload);
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        await fetchJobHistories();
      }
      return data;
    } catch (_) {
      return {'success': false, 'message': 'Koneksi ke server gagal.'};
    }
  }

  Future<void> fetchNotifications() async {
    try {
      final response = await _api.get('/notifications');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        _unreadNotificationsCount = data['unread_count'];
        final List list = data['notifications']['data'];
        final newNotifications = list.map((item) => NotificationModel.fromJson(item)).toList();

        // Cek notifikasi baru untuk membunyikan suara
        if (newNotifications.isNotEmpty) {
          final prefs = await SharedPreferences.getInstance();
          final List<String> seenIds = prefs.getStringList('seen_notification_ids') ?? [];

          final bool isFirstLoad = seenIds.isEmpty;
          bool hasNew = false;
          for (var notif in newNotifications) {
            if (!seenIds.contains(notif.id)) {
              seenIds.add(notif.id);
              hasNew = true;
              
              // Hanya bunyikan notifikasi jika bukan muatan pertama kali saat login/buka aplikasi
              if (!isFirstLoad) {
                await NotificationService.showNotification(
                  id: notif.id.hashCode,
                  title: notif.title,
                  body: notif.message,
                );
              }
            }
          }

          if (hasNew) {
            await prefs.setStringList('seen_notification_ids', seenIds);
          }
        }

        _notifications = newNotifications;
      }
    } catch (_) {}
    notifyListeners();
  }

  Future<void> markNotificationsRead() async {
    try {
      final response = await _api.post('/notifications/read-all', {});
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        _unreadNotificationsCount = 0;
        await fetchNotifications();
      }
    } catch (_) {}
  }

  Future<void> fetchBroadcasts() async {
    _isLoading = true;
    try {
      final response = await _api.get('/broadcasts');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        final List list = data['broadcasts']['data'];
        _allBroadcasts = list.map((item) => BroadcastModel.fromJson(item)).toList();
      }
    } catch (_) {}
    _isLoading = false;
    notifyListeners();
  }

  Future<Map<String, dynamic>> respondToBroadcast(String uuid, String status, String? notes, bool permitLetter) async {
    try {
      final response = await _api.post('/broadcasts/$uuid/respond', {
        'status': status,
        'notes': notes,
        'permit_letter': permitLetter,
      });
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        await fetchBroadcasts();
        await fetchDashboard();
      }
      return data;
    } catch (_) {
      return {'success': false, 'message': 'Koneksi ke server gagal.'};
    }
  }
}
