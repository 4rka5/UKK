class User {
  final int id;
  final String username;
  final String fullname;
  final String email;
  final String role;
  final String? status;

  User({
    required this.id,
    required this.username,
    required this.fullname,
    required this.email,
    required this.role,
    this.status,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      username: json['username'],
      fullname: json['fullname'],
      email: json['email'],
      role: json['role'],
      status: json['status'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'fullname': fullname,
      'email': email,
      'role': role,
      'status': status,
    };
  }
}
