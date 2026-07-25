import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import '../models/user.dart';

class AuthProvider extends ChangeNotifier {
  String? _token;
  UserModel? _user;
  bool _isLoading = false;

  AuthProvider() {
    _loadPersistedAuth();
  }

  String? get token => _token;
  UserModel? get user => _user;
  bool get isAuth => _token != null;
  bool get isLoading => _isLoading;

  Future<void> _loadPersistedAuth() async {
    final prefs = await SharedPreferences.getInstance();
    final savedToken = prefs.getString('auth_token');
    final savedUserJson = prefs.getString('auth_user');

    if (savedToken != null && savedUserJson != null) {
      _token = savedToken;
      _user = UserModel.fromJson(jsonDecode(savedUserJson));
      notifyListeners();
    }
  }

  Future<Map<String, dynamic>> login(String username, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final api = ApiService();
      final response = await api.post('/auth/login', {
        'username': username,
        'password': password,
      });

      dynamic data;
      try {
        data = jsonDecode(response.body);
      } catch (_) {
        _isLoading = false;
        notifyListeners();
        return {
          'success': false,
          'message': 'Respon server tidak valid (HTTP ${response.statusCode}). Silakan jalankan "git pull" & "php artisan migrate" di aaPanel.'
        };
      }

      if (response.statusCode == 200 && data['success'] == true) {
        _token = data['token'];
        _user = UserModel.fromJson(data['user']);

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('auth_user', jsonEncode(data['user']));

        _isLoading = false;
        notifyListeners();
        return {'success': true};
      } else {
        _isLoading = false;
        notifyListeners();
        return {'success': false, 'message': data['message'] ?? 'Login gagal.'};
      }
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      return {'success': false, 'message': 'Gagal menghubungkan ke server: $e'};
    }
  }

  Map<String, dynamic>? _publicSettings;
  Map<String, dynamic>? get publicSettings => _publicSettings;

  Future<void> fetchPublicSettings() async {
    try {
      final api = ApiService();
      final response = await api.get('/settings/public');
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        _publicSettings = data;
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> logout() async {
    if (_token != null) {
      try {
        final api = ApiService(token: _token);
        await api.post('/auth/logout', {});
      } catch (_) {}
    }

    _token = null;
    _user = null;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('auth_user');
    notifyListeners();
  }
}
