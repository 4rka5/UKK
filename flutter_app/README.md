# Project Management Flutter App - User Role

Flutter mobile application untuk role Designer/Developer dalam sistem project management.

## Features

- ✅ Login & Register
- ✅ Dashboard dengan statistik tasks
- ✅ List semua assigned tasks
- ✅ Detail task dengan subtasks & comments
- ✅ Update task status (todo → in_progress → done)
- ✅ Add comments ke task
- ✅ Update subtask status
- ✅ Filter tasks by status & priority
- ✅ Profile management

## Setup

1. Install dependencies:
```bash
flutter pub get
```

2. Update base URL di `lib/services/api_service.dart`:
```dart
// Ganti YOUR_IP dengan IP komputer Anda
static const String baseUrl = 'http://192.168.1.xxx:8001/api';
```

3. Run app:
```bash
flutter run
```

## API Server

Pastikan Laravel API server sudah running:
```bash
cd c:\xampp\htdocs\UKK\p2
php artisan serve --host=0.0.0.0 --port=8001
```

## Credentials Testing

- Username: `desainer` 
- Password: `password`

atau

- Email: `desainer@example.com`
- Password: `password`
