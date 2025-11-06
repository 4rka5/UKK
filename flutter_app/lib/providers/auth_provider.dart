import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';

class AuthProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  final StorageService _storageService = StorageService();

  User? _user;
  bool _isLoading = false;
  String? _errorMessage;

  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _user != null;

  // Initialize - check if user is already logged in
  Future<void> init() async {
    final token = await _storageService.getToken();
    if (token != null) {
      try {
        final response = await _apiService.getUser();
        if (response['success']) {
          _user = User.fromJson(response['data']);
          notifyListeners();
        }
      } catch (e) {
        // Token invalid, clear storage
        await _storageService.clearAll();
      }
    }
  }

  // Login
  Future<bool> login(String login, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.login(login, password);
      
      if (response['success']) {
        final token = response['data']['token'];
        final userData = response['data']['user'];
        
        await _storageService.saveToken(token);
        await _storageService.saveUserData(jsonEncode(userData));
        
        _user = User.fromJson(userData);
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response['message'] ?? 'Login failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } on DioException catch (e) {
      if (e.type == DioExceptionType.connectionTimeout) {
        _errorMessage = 'Connection timeout. Check your internet connection.';
      } else if (e.type == DioExceptionType.receiveTimeout) {
        _errorMessage = 'Server response timeout. Try again.';
      } else if (e.type == DioExceptionType.connectionError) {
        _errorMessage = 'Cannot connect to server. Make sure API server is running on http://localhost:8001';
      } else if (e.response?.statusCode == 401) {
        _errorMessage = 'Invalid credentials';
      } else if (e.response?.statusCode == 422) {
        _errorMessage = e.response?.data['message'] ?? 'Validation error';
      } else {
        _errorMessage = 'Network error: ${e.message}';
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = 'Unexpected error: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Register
  Future<bool> register({
    required String fullname,
    required String username,
    required String email,
    required String password,
    String role = 'developer',
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.register(
        fullname: fullname,
        username: username,
        email: email,
        password: password,
        role: role,
      );
      
      if (response['success']) {
        final token = response['data']['token'];
        final userData = response['data']['user'];
        
        await _storageService.saveToken(token);
        await _storageService.saveUserData(jsonEncode(userData));
        
        _user = User.fromJson(userData);
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = response['message'] ?? 'Registration failed';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } on DioException catch (e) {
      if (e.type == DioExceptionType.connectionTimeout) {
        _errorMessage = 'Connection timeout. Check your internet connection.';
      } else if (e.type == DioExceptionType.receiveTimeout) {
        _errorMessage = 'Server response timeout. Try again.';
      } else if (e.type == DioExceptionType.connectionError) {
        _errorMessage = 'Cannot connect to server. Make sure API server is running on http://localhost:8001';
      } else if (e.response?.statusCode == 422) {
        _errorMessage = e.response?.data['message'] ?? 'Validation error';
      } else {
        _errorMessage = 'Network error: ${e.message}';
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = 'Unexpected error: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Logout
  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      // Ignore error, just clear local data
    }
    
    await _storageService.clearAll();
    _user = null;
    notifyListeners();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
