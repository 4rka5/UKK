import 'package:dio/dio.dart';
import 'storage_service.dart';

class ApiService {
  // PILIH BASE URL SESUAI DEVICE:
  // Web Browser (Chrome/Edge): localhost
  // Android Emulator: 10.0.2.2
  // iOS Simulator: localhost
  // Physical Device: IP komputer Anda (cek dengan ipconfig)
  
  static const String baseUrl = 'http://localhost:8001/api'; // Web Browser / iOS Simulator
  // static const String baseUrl = 'http://10.0.2.2:8001/api'; // Android Emulator
  // static const String baseUrl = 'http://192.168.1.100:8001/api'; // Real Device (ganti IP)
  
  late Dio _dio;
  final StorageService _storage = StorageService();

  ApiService() {
    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    // Interceptor untuk auto-add token
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _storage.getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (error, handler) {
        if (error.response?.statusCode == 401) {
          // Token expired or invalid
          _storage.clearAll();
        }
        return handler.next(error);
      },
    ));
  }

  // Auth APIs
  Future<Map<String, dynamic>> login(String login, String password) async {
    try {
      final response = await _dio.post('/login', data: {
        'login': login,
        'password': password,
      });
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> register({
    required String fullname,
    required String username,
    required String email,
    required String password,
    String role = 'developer',
  }) async {
    try {
      final response = await _dio.post('/register', data: {
        'fullname': fullname,
        'username': username,
        'email': email,
        'password': password,
        'role': role,
      });
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> logout() async {
    try {
      final response = await _dio.post('/logout');
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getUser() async {
    try {
      final response = await _dio.get('/user');
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  // Dashboard APIs
  Future<Map<String, dynamic>> getDashboardStats() async {
    try {
      final response = await _dio.get('/dashboard/stats');
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getMyCards({String? status}) async {
    try {
      final queryParams = <String, dynamic>{};
      if (status != null) {
        queryParams['status'] = status;
      }
      final response = await _dio.get('/my-cards', queryParameters: queryParams);
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  // Card APIs
  Future<Map<String, dynamic>> getCardDetail(int cardId) async {
    try {
      final response = await _dio.get('/cards/$cardId');
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> updateCardStatus(int cardId, String status) async {
    try {
      final response = await _dio.put('/cards/$cardId/status', data: {
        'status': status,
      });
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  // Comment APIs
  Future<Map<String, dynamic>> getComments(int cardId) async {
    try {
      final response = await _dio.get('/cards/$cardId/comments');
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> addComment(int cardId, String commentText) async {
    try {
      final response = await _dio.post('/cards/$cardId/comments', data: {
        'comment_text': commentText,
        'comment_type': 'card',
      });
      return response.data;
    } catch (e) {
      rethrow;
    }
  }

  // Subtask APIs
  Future<Map<String, dynamic>> updateSubtask(
    int subtaskId,
    String status,
  ) async {
    try {
      final response = await _dio.put('/subtasks/$subtaskId', data: {
        'status': status,
      });
      return response.data;
    } catch (e) {
      rethrow;
    }
  }
}
