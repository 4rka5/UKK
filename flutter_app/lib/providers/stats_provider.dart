import 'package:flutter/material.dart';
import '../services/api_service.dart';

class StatsProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();

  Map<String, dynamic> _stats = {};
  bool _isLoading = false;
  String? _errorMessage;

  Map<String, dynamic> get stats => _stats;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  // Get individual stats
  int get totalTasks => _stats['total_cards'] ?? 0;
  int get completedTasks => _stats['completed_cards'] ?? 0;
  int get inProgressTasks => _stats['in_progress_cards'] ?? 0;
  int get overdueTasks => _stats['overdue_cards'] ?? 0;
  int get totalProjects => _stats['total_projects'] ?? 0;

  // Fetch dashboard stats
  Future<void> fetchStats() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.getDashboardStats();

      if (response['success']) {
        _stats = response['data'] ?? {};
        _isLoading = false;
        notifyListeners();
      } else {
        _errorMessage = response['message'] ?? 'Failed to fetch stats';
        _isLoading = false;
        notifyListeners();
      }
    } catch (e) {
      _errorMessage = 'Connection error: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
    }
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
