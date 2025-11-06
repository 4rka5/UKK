class TaskCard {
  final int id;
  final String cardTitle;
  final String? description;
  final String status;
  final String priority;
  final String? dueDate;
  final int? estimatedHours;
  final int? actualHours;
  final Board? board;
  final List<TaskUser>? assignees;
  final List<Subtask>? subtasks;
  final List<TaskComment>? comments;
  final DateTime createdAt;
  final DateTime updatedAt;

  TaskCard({
    required this.id,
    required this.cardTitle,
    this.description,
    required this.status,
    required this.priority,
    this.dueDate,
    this.estimatedHours,
    this.actualHours,
    this.board,
    this.assignees,
    this.subtasks,
    this.comments,
    required this.createdAt,
    required this.updatedAt,
  });

  factory TaskCard.fromJson(Map<String, dynamic> json) {
    return TaskCard(
      id: json['id'],
      cardTitle: json['card_title'],
      description: json['description'],
      status: json['status'],
      priority: json['priority'],
      dueDate: json['due_date'],
      estimatedHours: json['estimated_hours'],
      actualHours: json['actual_hours'],
      board: json['board'] != null ? Board.fromJson(json['board']) : null,
      assignees: json['assignees'] != null
          ? (json['assignees'] as List).map((e) => TaskUser.fromJson(e)).toList()
          : null,
      subtasks: json['subtasks'] != null
          ? (json['subtasks'] as List).map((e) => Subtask.fromJson(e)).toList()
          : null,
      comments: json['comments'] != null
          ? (json['comments'] as List).map((e) => TaskComment.fromJson(e)).toList()
          : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  bool get isOverdue {
    if (dueDate == null) return false;
    return DateTime.parse(dueDate!).isBefore(DateTime.now()) && 
           status != 'done';
  }

  int get completedSubtasks {
    if (subtasks == null) return 0;
    return subtasks!.where((s) => s.status == 'done').length;
  }

  int get totalSubtasks {
    return subtasks?.length ?? 0;
  }

  String get statusDisplay {
    switch (status) {
      case 'backlog': return 'Backlog';
      case 'todo': return 'To Do';
      case 'in_progress': return 'In Progress';
      case 'code_review': return 'Code Review';
      case 'testing': return 'Testing';
      case 'done': return 'Done';
      default: return status;
    }
  }

  String get priorityDisplay {
    switch (priority) {
      case 'low': return 'Low';
      case 'medium': return 'Medium';
      case 'high': return 'High';
      default: return priority;
    }
  }

  // Getter aliases for easier access
  String get taskTitle => cardTitle;
  
  String get dueDateDisplay {
    if (dueDate == null) return '';
    final date = DateTime.parse(dueDate!);
    final now = DateTime.now();
    final diff = date.difference(now);
    
    if (diff.inDays == 0) {
      return 'Today';
    } else if (diff.inDays == 1) {
      return 'Tomorrow';
    } else if (diff.inDays > 1 && diff.inDays < 7) {
      return '${diff.inDays} days';
    } else {
      return '${date.day}/${date.month}/${date.year}';
    }
  }
  
  String get dueDateFormatted {
    if (dueDate == null) return '';
    final date = DateTime.parse(dueDate!);
    return '${date.day}/${date.month}/${date.year}';
  }
}

class Board {
  final int id;
  final String boardName;
  final String? description;
  final Project? project;

  Board({
    required this.id,
    required this.boardName,
    this.description,
    this.project,
  });

  factory Board.fromJson(Map<String, dynamic> json) {
    return Board(
      id: json['id'],
      boardName: json['board_name'],
      description: json['description'],
      project: json['project'] != null ? Project.fromJson(json['project']) : null,
    );
  }
  
  // Getter alias for easier access
  String get name => boardName;
}

class Project {
  final int id;
  final String projectName;
  final String? description;

  Project({
    required this.id,
    required this.projectName,
    this.description,
  });

  factory Project.fromJson(Map<String, dynamic> json) {
    return Project(
      id: json['id'],
      projectName: json['project_name'],
      description: json['description'],
    );
  }
  
  // Getter alias for easier access
  String get name => projectName;
}

class TaskUser {
  final int id;
  final String fullname;
  final String? username;

  TaskUser({
    required this.id,
    required this.fullname,
    this.username,
  });

  factory TaskUser.fromJson(Map<String, dynamic> json) {
    return TaskUser(
      id: json['id'],
      fullname: json['fullname'],
      username: json['username'],
    );
  }
}

class Subtask {
  final int id;
  final String subtaskTitle;
  final String? description;
  final String status;
  final int? estimatedHours;

  Subtask({
    required this.id,
    required this.subtaskTitle,
    this.description,
    required this.status,
    this.estimatedHours,
  });

  factory Subtask.fromJson(Map<String, dynamic> json) {
    return Subtask(
      id: json['id'],
      subtaskTitle: json['subtask_title'],
      description: json['description'],
      status: json['status'],
      estimatedHours: json['estimated_hours'],
    );
  }
  
  String get statusDisplay {
    switch (status) {
      case 'todo': return 'To Do';
      case 'in_progress': return 'In Progress';
      case 'done': return 'Done';
      default: return status;
    }
  }
}

class TaskComment {
  final int id;
  final String commentText;
  final TaskUser user;
  final DateTime createdAt;

  TaskComment({
    required this.id,
    required this.commentText,
    required this.user,
    required this.createdAt,
  });

  factory TaskComment.fromJson(Map<String, dynamic> json) {
    return TaskComment(
      id: json['id'],
      commentText: json['comment_text'],
      user: TaskUser.fromJson(json['user']),
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}
