import 'package:flutter/material.dart';
import '../models/task_card.dart';
import '../services/api_service.dart';

class TaskProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<TaskCard> _tasks = [];
  TaskCard? _selectedTask;
  bool _isLoading = false;
  String? _errorMessage;

  // Filters
  String? _statusFilter;
  bool _assignedToMeOnly = true;

  List<TaskCard> get tasks => _tasks;
  TaskCard? get selectedTask => _selectedTask;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String? get statusFilter => _statusFilter;
  bool get assignedToMeOnly => _assignedToMeOnly;

  // Get filtered tasks
  List<TaskCard> get filteredTasks {
    var filtered = _tasks;
    
    if (_statusFilter != null && _statusFilter!.isNotEmpty) {
      filtered = filtered.where((task) => task.status == _statusFilter).toList();
    }
    
    return filtered;
  }

  // Fetch my cards
  Future<void> fetchMyCards() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.getMyCards(
        status: _statusFilter,
      );

      if (response['success']) {
        final List<dynamic> cardsJson = response['data'] ?? [];
        _tasks = cardsJson.map((json) => TaskCard.fromJson(json)).toList();
        _isLoading = false;
        notifyListeners();
      } else {
        _errorMessage = response['message'] ?? 'Failed to fetch tasks';
        _isLoading = false;
        notifyListeners();
      }
    } catch (e) {
      _errorMessage = 'Connection error: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
    }
  }

  // Fetch card detail
  Future<void> fetchCardDetail(int cardId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.getCardDetail(cardId);

      if (response['success']) {
        _selectedTask = TaskCard.fromJson(response['data']);
        _isLoading = false;
        notifyListeners();
      } else {
        _errorMessage = response['message'] ?? 'Failed to fetch task details';
        _isLoading = false;
        notifyListeners();
      }
    } catch (e) {
      _errorMessage = 'Connection error: ${e.toString()}';
      _isLoading = false;
      notifyListeners();
    }
  }

  // Update card status
  Future<bool> updateStatus(int cardId, String status) async {
    try {
      final response = await _apiService.updateCardStatus(cardId, status);

      if (response['success']) {
        // Update local task
        final index = _tasks.indexWhere((task) => task.id == cardId);
        if (index != -1) {
          _tasks[index] = TaskCard.fromJson(response['data']);
        }
        
        // Update selected task if it's the same
        if (_selectedTask?.id == cardId) {
          _selectedTask = TaskCard.fromJson(response['data']);
        }
        
        notifyListeners();
        return true;
      } else {
        _errorMessage = response['message'] ?? 'Failed to update status';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Connection error: ${e.toString()}';
      notifyListeners();
      return false;
    }
  }

  // Add comment
  Future<bool> addComment(int cardId, String comment) async {
    try {
      final response = await _apiService.addComment(cardId, comment);

      if (response['success']) {
        // Refresh card detail to show new comment
        await fetchCardDetail(cardId);
        return true;
      } else {
        _errorMessage = response['message'] ?? 'Failed to add comment';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Connection error: ${e.toString()}';
      notifyListeners();
      return false;
    }
  }

  // Update subtask
  Future<bool> updateSubtask(int cardId, int subtaskId, String status) async {
    try {
      final response = await _apiService.updateSubtask(subtaskId, status);

      if (response['success']) {
        // Refresh card detail to show updated subtask
        await fetchCardDetail(cardId);
        return true;
      } else {
        _errorMessage = response['message'] ?? 'Failed to update subtask';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Connection error: ${e.toString()}';
      notifyListeners();
      return false;
    }
  }

  // Set status filter
  void setStatusFilter(String? status) {
    _statusFilter = status;
    notifyListeners();
    fetchMyCards();
  }

  // Clear status filter
  void clearFilter() {
    _statusFilter = null;
    notifyListeners();
    fetchMyCards();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  void clearSelectedTask() {
    _selectedTask = null;
    notifyListeners();
  }
}
