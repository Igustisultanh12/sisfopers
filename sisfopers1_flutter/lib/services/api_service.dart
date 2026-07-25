import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String defaultBaseUrl = 'https://sisfoperskc.my.id/api';
  final String _baseUrl;
  final String? _token;

  ApiService({String? baseUrl, String? token})
      : _baseUrl = baseUrl ?? defaultBaseUrl,
        _token = token;

  Map<String, String> _getHeaders() {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (_token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  Future<http.Response> get(String endpoint) async {
    final url = Uri.parse('$_baseUrl$endpoint');
    return await http.get(url, headers: _getHeaders());
  }

  Future<http.Response> post(String endpoint, Map<String, dynamic> body) async {
    final url = Uri.parse('$_baseUrl$endpoint');
    return await http.post(
      url,
      headers: _getHeaders(),
      body: jsonEncode(body),
    );
  }
}
