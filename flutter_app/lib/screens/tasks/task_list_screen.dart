import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/task_provider.dart';
import 'task_detail_screen.dart';

class TaskListScreen extends StatefulWidget {
  const TaskListScreen({super.key});

  @override
  State<TaskListScreen> createState() => _TaskListScreenState();
}

class _TaskListScreenState extends State<TaskListScreen> {
  String? _selectedStatus;

  final List<String> _statuses = [
    'backlog',
    'todo',
    'in_progress',
    'code_review',
    'testing',
    'done',
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Tasks'),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.filter_list),
            onSelected: (value) {
              setState(() {
                _selectedStatus = value == 'all' ? null : value;
              });
              context.read<TaskProvider>().setStatusFilter(_selectedStatus);
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'all',
                child: Text('All Status'),
              ),
              ..._statuses.map((status) => PopupMenuItem(
                    value: status,
                    child: Text(_getStatusDisplay(status)),
                  )),
            ],
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => context.read<TaskProvider>().fetchMyCards(),
        child: Consumer<TaskProvider>(
          builder: (context, taskProvider, _) {
            if (taskProvider.isLoading) {
              return const Center(child: CircularProgressIndicator());
            }

            if (taskProvider.errorMessage != null) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                    const SizedBox(height: 16),
                    Text(
                      taskProvider.errorMessage!,
                      style: TextStyle(color: Colors.grey[600]),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton.icon(
                      onPressed: () => taskProvider.fetchMyCards(),
                      icon: const Icon(Icons.refresh),
                      label: const Text('Retry'),
                    ),
                  ],
                ),
              );
            }

            final tasks = taskProvider.filteredTasks;

            if (tasks.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.inbox_outlined,
                        size: 100, color: Colors.grey[300]),
                    const SizedBox(height: 16),
                    Text(
                      _selectedStatus == null
                          ? 'No tasks assigned to you'
                          : 'No tasks with status "${_getStatusDisplay(_selectedStatus!)}"',
                      style: TextStyle(
                        fontSize: 16,
                        color: Colors.grey[600],
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              );
            }

            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: tasks.length,
              itemBuilder: (context, index) {
                final task = tasks[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: InkWell(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => TaskDetailScreen(taskId: task.id),
                        ),
                      );
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  task.taskTitle,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: _getPriorityColor(task.priority),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  task.priorityDisplay,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Icon(
                                Icons.dashboard,
                                size: 14,
                                color: Colors.grey[600],
                              ),
                              const SizedBox(width: 4),
                              Text(
                                task.board?.name ?? 'No Board',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey[600],
                                ),
                              ),
                              const SizedBox(width: 12),
                              Icon(
                                Icons.folder,
                                size: 14,
                                color: Colors.grey[600],
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  task.board?.project?.name ?? 'No Project',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey[600],
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          if (task.description != null &&
                              task.description!.isNotEmpty) ...[
                            const SizedBox(height: 8),
                            Text(
                              task.description!,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey[700],
                              ),
                            ),
                          ],
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color: _getStatusColor(task.status)
                                      .withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                    color: _getStatusColor(task.status),
                                    width: 1,
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      _getStatusIcon(task.status),
                                      size: 14,
                                      color: _getStatusColor(task.status),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      task.statusDisplay,
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: _getStatusColor(task.status),
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const Spacer(),
                              if (task.subtasks != null &&
                                  task.subtasks!.isNotEmpty) ...[
                                Icon(Icons.checklist, size: 16, color: Colors.grey[600]),
                                const SizedBox(width: 4),
                                Text(
                                  '${task.completedSubtasks}/${task.totalSubtasks}',
                                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                                ),
                                const SizedBox(width: 12),
                              ],
                              if (task.dueDate != null) ...[
                                Icon(
                                  Icons.schedule,
                                  size: 16,
                                  color: task.isOverdue ? Colors.red : Colors.grey[600],
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  task.dueDateDisplay,
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: task.isOverdue ? Colors.red : Colors.grey[600],
                                    fontWeight: task.isOverdue ? FontWeight.bold : null,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  String _getStatusDisplay(String status) {
    switch (status) {
      case 'backlog':
        return 'Backlog';
      case 'todo':
        return 'To Do';
      case 'in_progress':
        return 'In Progress';
      case 'code_review':
        return 'Code Review';
      case 'testing':
        return 'Testing';
      case 'done':
        return 'Done';
      default:
        return status;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'backlog':
        return Colors.grey;
      case 'todo':
        return Colors.blue;
      case 'in_progress':
        return Colors.orange;
      case 'code_review':
        return Colors.purple;
      case 'testing':
        return Colors.teal;
      case 'done':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'backlog':
        return Icons.inbox;
      case 'todo':
        return Icons.list;
      case 'in_progress':
        return Icons.pending;
      case 'code_review':
        return Icons.rate_review;
      case 'testing':
        return Icons.bug_report;
      case 'done':
        return Icons.check_circle;
      default:
        return Icons.help;
    }
  }

  Color _getPriorityColor(String priority) {
    switch (priority) {
      case 'low':
        return Colors.green;
      case 'medium':
        return Colors.orange;
      case 'high':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
