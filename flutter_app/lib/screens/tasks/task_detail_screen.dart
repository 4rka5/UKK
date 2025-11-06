import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/task_provider.dart';
import 'package:intl/intl.dart';

class TaskDetailScreen extends StatefulWidget {
  final int taskId;

  const TaskDetailScreen({super.key, required this.taskId});

  @override
  State<TaskDetailScreen> createState() => _TaskDetailScreenState();
}

class _TaskDetailScreenState extends State<TaskDetailScreen> {
  final _commentController = TextEditingController();
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TaskProvider>().fetchCardDetail(widget.taskId);
    });
  }

  @override
  void dispose() {
    _commentController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _updateStatus(String status) async {
    final success = await context.read<TaskProvider>().updateStatus(
          widget.taskId,
          status,
        );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Status updated successfully')),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            context.read<TaskProvider>().errorMessage ?? 'Failed to update status',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _addComment() async {
    if (_commentController.text.trim().isEmpty) return;

    final success = await context.read<TaskProvider>().addComment(
          widget.taskId,
          _commentController.text.trim(),
        );

    if (success && mounted) {
      _commentController.clear();
      FocusScope.of(context).unfocus();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Comment added')),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            context.read<TaskProvider>().errorMessage ?? 'Failed to add comment',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _updateSubtask(int subtaskId, String currentStatus) async {
    final newStatus = currentStatus == 'done' ? 'todo' : 'done';
    
    final success = await context.read<TaskProvider>().updateSubtask(
          widget.taskId,
          subtaskId,
          newStatus,
        );

    if (!success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            context.read<TaskProvider>().errorMessage ?? 'Failed to update subtask',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Task Details'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              context.read<TaskProvider>().fetchCardDetail(widget.taskId);
            },
          ),
        ],
      ),
      body: Consumer<TaskProvider>(
        builder: (context, taskProvider, _) {
          if (taskProvider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          final task = taskProvider.selectedTask;

          if (task == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  const Text('Task not found'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Go Back'),
                  ),
                ],
              ),
            );
          }

          return ListView(
            controller: _scrollController,
            padding: const EdgeInsets.all(16),
            children: [
              // Title and Priority
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Text(
                      task.taskTitle,
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: _getPriorityColor(task.priority),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      task.priorityDisplay,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Project and Board Info
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildInfoRow(
                        Icons.folder,
                        'Project',
                        task.board?.project?.name ?? 'No Project',
                      ),
                      const Divider(height: 16),
                      _buildInfoRow(
                        Icons.dashboard,
                        'Board',
                        task.board?.name ?? 'No Board',
                      ),
                      if (task.dueDate != null) ...[
                        const Divider(height: 16),
                        _buildInfoRow(
                          Icons.schedule,
                          'Due Date',
                          task.dueDateFormatted,
                          isOverdue: task.isOverdue,
                        ),
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Description
              if (task.description != null && task.description!.isNotEmpty) ...[
                Text(
                  'Description',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                const SizedBox(height: 8),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Text(task.description!),
                  ),
                ),
                const SizedBox(height: 16),
              ],

              // Status Selector
              Text(
                'Status',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 8),
              _buildStatusSelector(task.status),
              const SizedBox(height: 16),

              // Subtasks
              if (task.subtasks != null && task.subtasks!.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Subtasks',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                    Text(
                      '${task.completedSubtasks}/${task.totalSubtasks} completed',
                      style: TextStyle(
                        color: Colors.grey[600],
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Card(
                  child: Column(
                    children: task.subtasks!
                        .map((subtask) => CheckboxListTile(
                              value: subtask.status == 'done',
                              onChanged: (_) => _updateSubtask(subtask.id, subtask.status),
                              title: Text(subtask.subtaskTitle),
                              subtitle: subtask.description != null
                                  ? Text(subtask.description!)
                                  : null,
                              secondary: Icon(
                                subtask.status == 'done'
                                    ? Icons.check_circle
                                    : Icons.radio_button_unchecked,
                                color: subtask.status == 'done'
                                    ? Colors.green
                                    : Colors.grey,
                              ),
                            ))
                        .toList(),
                  ),
                ),
                const SizedBox(height: 16),
              ],

              // Comments
              Text(
                'Comments',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 8),
              if (task.comments != null && task.comments!.isNotEmpty)
                ...task.comments!.map((comment) => Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                CircleAvatar(
                                  radius: 16,
                                  backgroundColor: Theme.of(context).primaryColor,
                                  child: Text(
                                    comment.user.fullname.substring(0, 1).toUpperCase(),
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        comment.user.fullname,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      Text(
                                        DateFormat('MMM dd, yyyy HH:mm')
                                            .format(comment.createdAt),
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey[600],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(comment.commentText),
                          ],
                        ),
                      ),
                    )),
              if (task.comments == null || task.comments!.isEmpty)
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Center(
                      child: Text(
                        'No comments yet',
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                    ),
                  ),
                ),
              const SizedBox(height: 80), // Space for bottom input
            ],
          );
        },
      ),
      bottomSheet: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 4,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: SafeArea(
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _commentController,
                  decoration: InputDecoration(
                    hintText: 'Add a comment...',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(24),
                    ),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                  ),
                  maxLines: null,
                  textInputAction: TextInputAction.send,
                  onSubmitted: (_) => _addComment(),
                ),
              ),
              const SizedBox(width: 8),
              FloatingActionButton(
                onPressed: _addComment,
                mini: true,
                child: const Icon(Icons.send),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value,
      {bool isOverdue = false}) {
    return Row(
      children: [
        Icon(icon, size: 20, color: isOverdue ? Colors.red : Colors.grey[600]),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
            Text(
              value,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: isOverdue ? Colors.red : null,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatusSelector(String currentStatus) {
    final statuses = [
      {'value': 'backlog', 'label': 'Backlog', 'icon': Icons.inbox},
      {'value': 'todo', 'label': 'To Do', 'icon': Icons.list},
      {'value': 'in_progress', 'label': 'In Progress', 'icon': Icons.pending},
      {'value': 'code_review', 'label': 'Code Review', 'icon': Icons.rate_review},
      {'value': 'testing', 'label': 'Testing', 'icon': Icons.bug_report},
      {'value': 'done', 'label': 'Done', 'icon': Icons.check_circle},
    ];

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(8),
        child: Wrap(
          spacing: 8,
          runSpacing: 8,
          children: statuses.map((status) {
            final isSelected = currentStatus == status['value'];
            return FilterChip(
              label: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    status['icon'] as IconData,
                    size: 16,
                    color: isSelected ? Colors.white : _getStatusColor(status['value'] as String),
                  ),
                  const SizedBox(width: 4),
                  Text(status['label'] as String),
                ],
              ),
              selected: isSelected,
              onSelected: (_) => _updateStatus(status['value'] as String),
              selectedColor: _getStatusColor(status['value'] as String),
              labelStyle: TextStyle(
                color: isSelected ? Colors.white : null,
                fontWeight: isSelected ? FontWeight.bold : null,
              ),
            );
          }).toList(),
        ),
      ),
    );
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
