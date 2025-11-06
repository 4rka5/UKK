import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../auth/login_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final user = authProvider.user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Profile Header
          Card(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 50,
                    backgroundColor: Theme.of(context).primaryColor,
                    child: Text(
                      user?.fullname.substring(0, 1).toUpperCase() ?? 'U',
                      style: const TextStyle(
                        fontSize: 36,
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    user?.fullname ?? '',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '@${user?.username ?? ''}',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: _getRoleColor(user?.role ?? ''),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      user?.role.toUpperCase() ?? '',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Account Information
          Text(
            'Account Information',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.email),
                  title: const Text('Email'),
                  subtitle: Text(user?.email ?? ''),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.person),
                  title: const Text('Username'),
                  subtitle: Text(user?.username ?? ''),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.work),
                  title: const Text('Role'),
                  subtitle: Text(_getRoleDisplay(user?.role ?? '')),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Icon(
                    Icons.circle,
                    color: user?.status == 'active' ? Colors.green : Colors.red,
                    size: 12,
                  ),
                  title: const Text('Status'),
                  subtitle: Text(user?.status?.toUpperCase() ?? ''),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Actions
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.info_outline),
                  title: const Text('About'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () {
                    showAboutDialog(
                      context: context,
                      applicationName: 'Project Management',
                      applicationVersion: '1.0.0',
                      applicationLegalese: '© 2025 Project Management App',
                      children: [
                        const SizedBox(height: 16),
                        const Text(
                          'A task management application for designers and developers to collaborate on projects.',
                        ),
                      ],
                    );
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Logout Button
          ElevatedButton.icon(
            onPressed: () async {
              final confirm = await showDialog<bool>(
                context: context,
                builder: (context) => AlertDialog(
                  title: const Text('Logout'),
                  content: const Text('Are you sure you want to logout?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context, false),
                      child: const Text('Cancel'),
                    ),
                    ElevatedButton(
                      onPressed: () => Navigator.pop(context, true),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('Logout'),
                    ),
                  ],
                ),
              );

              if (confirm == true && context.mounted) {
                await authProvider.logout();
                if (context.mounted) {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (route) => false,
                  );
                }
              }
            },
            icon: const Icon(Icons.logout),
            label: const Text('Logout'),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
          ),
        ],
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

  String _getRoleDisplay(String role) {
    switch (role.toLowerCase()) {
      case 'admin':
        return 'Administrator';
      case 'team_lead':
        return 'Team Lead';
      case 'designer':
        return 'Designer';
      case 'developer':
        return 'Developer';
      default:
        return role;
    }
  }
}
