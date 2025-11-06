import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/stats_provider.dart';
import '../../providers/task_provider.dart';
import '../tasks/task_list_screen.dart';
import '../profile/profile_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StatsProvider>().fetchStats();
      context.read<TaskProvider>().fetchMyCards();
    });
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }
  
  // Public method untuk switch tab dari child widget
  void switchToTab(int index) {
    _onItemTapped(index);
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> pages = [
      const DashboardTab(),
      const TaskListScreen(),
      const ProfileScreen(),
    ];

    return Scaffold(
      body: pages[_selectedIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: _onItemTapped,
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard_outlined),
            selectedIcon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          NavigationDestination(
            icon: Icon(Icons.task_outlined),
            selectedIcon: Icon(Icons.task),
            label: 'My Tasks',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}

class DashboardTab extends StatelessWidget {
  const DashboardTab({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final user = authProvider.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              context.read<StatsProvider>().fetchStats();
              context.read<TaskProvider>().fetchMyCards();
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await Future.wait([
            context.read<StatsProvider>().fetchStats(),
            context.read<TaskProvider>().fetchMyCards(),
          ]);
        },
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 30,
                          backgroundColor: Theme.of(context).primaryColor,
                          child: Text(
                            user?.fullname.substring(0, 1).toUpperCase() ?? 'U',
                            style: const TextStyle(
                              fontSize: 24,
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Welcome back,',
                                style: Theme.of(context).textTheme.bodySmall,
                              ),
                              Text(
                                user?.fullname ?? '',
                                style: Theme.of(context)
                                    .textTheme
                                    .titleLarge
                                    ?.copyWith(
                                      fontWeight: FontWeight.bold,
                                    ),
                              ),
                              Container(
                                margin: const EdgeInsets.only(top: 4),
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: _getRoleColor(user?.role ?? ''),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  user?.role.toUpperCase() ?? '',
                                  style: const TextStyle(
                                    fontSize: 10,
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Your Statistics',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 12),
            Consumer<StatsProvider>(
              builder: (context, statsProvider, _) {
                if (statsProvider.isLoading) {
                  return const Center(
                    child: Padding(
                      padding: EdgeInsets.all(32),
                      child: CircularProgressIndicator(),
                    ),
                  );
                }

                return GridView.count(
                  crossAxisCount: 2,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  mainAxisSpacing: 12,
                  crossAxisSpacing: 12,
                  childAspectRatio: 1.5,
                  children: [
                    _buildStatCard(
                      context,
                      'Total Tasks',
                      statsProvider.totalTasks.toString(),
                      Icons.task_alt,
                      Colors.blue,
                    ),
                    _buildStatCard(
                      context,
                      'In Progress',
                      statsProvider.inProgressTasks.toString(),
                      Icons.pending_actions,
                      Colors.orange,
                    ),
                    _buildStatCard(
                      context,
                      'Completed',
                      statsProvider.completedTasks.toString(),
                      Icons.check_circle,
                      Colors.green,
                    ),
                    _buildStatCard(
                      context,
                      'Overdue',
                      statsProvider.overdueTasks.toString(),
                      Icons.warning,
                      Colors.red,
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Recent Tasks',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                TextButton(
                  onPressed: () {
                    // Navigate to tasks tab
                    final homeScreenState = context.findAncestorStateOfType<_HomeScreenState>();
                    if (homeScreenState != null) {
                      homeScreenState.switchToTab(1);
                    }
                  },
                  child: const Text('View All'),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Consumer<TaskProvider>(
              builder: (context, taskProvider, _) {
                if (taskProvider.isLoading) {
                  return const Center(
                    child: Padding(
                      padding: EdgeInsets.all(32),
                      child: CircularProgressIndicator(),
                    ),
                  );
                }

                final tasks = taskProvider.tasks.take(5).toList();

                if (tasks.isEmpty) {
                  return Card(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        children: [
                          Icon(
                            Icons.inbox_outlined,
                            size: 64,
                            color: Colors.grey[400],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'No tasks assigned',
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                return Column(
                  children: tasks
                      .map((task) => Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: _getStatusColor(task.status),
                                child: Icon(
                                  _getStatusIcon(task.status),
                                  color: Colors.white,
                                  size: 20,
                                ),
                              ),
                              title: Text(
                                task.taskTitle,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              subtitle: Text(task.board?.name ?? 'No Board'),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: _getPriorityColor(task.priority),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Text(
                                      task.priorityDisplay,
                                      style: const TextStyle(
                                        fontSize: 10,
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                  if (task.dueDate != null)
                                    Text(
                                      task.dueDateDisplay,
                                      style: TextStyle(
                                        fontSize: 10,
                                        color: task.isOverdue
                                            ? Colors.red
                                            : Colors.grey,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ))
                      .toList(),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(
    BuildContext context,
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              value,
              style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: Theme.of(context).textTheme.bodySmall,
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Color _getRoleColor(String role) {
    switch (role.toLowerCase()) {
      case 'admin':
        return Colors.purple;
      case 'team_lead':
        return Colors.blue;
      case 'designer':
        return Colors.pink;
      case 'developer':
        return Colors.green;
      default:
        return Colors.grey;
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
