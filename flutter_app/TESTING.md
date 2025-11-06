# Flutter App - Testing Guide

Panduan lengkap untuk testing aplikasi Flutter Project Management.

## 🚀 Quick Start

### 1. Preparation

**Install Dependencies:**
```powershell
cd c:\xampp\htdocs\UKK\flutter_app
flutter pub get
```

**Start API Server:**
```powershell
cd c:\xampp\htdocs\UKK\p2
php artisan serve --port=8001
```

### 2. Configure Base URL

Edit `lib/services/api_service.dart` line 8:

**For Android Emulator:**
```dart
static const String baseUrl = 'http://10.0.2.2:8001/api';
```

**For Physical Android Device:**
```dart
static const String baseUrl = 'http://192.168.1.100:8001/api';  // Ganti dengan IP PC Anda
```

Cara cek IP PC:
```powershell
ipconfig
# Cari IPv4 Address di bagian Wireless LAN adapter Wi-Fi
```

**For iOS Simulator:**
```dart
static const String baseUrl = 'http://localhost:8001/api';
```

### 3. Run the App

```powershell
flutter run
```

Atau pilih device di VS Code dan tekan F5.

## 📝 Test Scenarios

### Scenario 1: Login

1. **Buka app** → Tampil Login Screen
2. **Input credentials:**
   - Username: `desainer`
   - Password: `password`
3. **Tap Login**
4. **Expected:** Navigate ke Dashboard dengan welcome card

**Alternative Test:**
- Login dengan email: `desainer@example.com`
- Login dengan developer: `developer` / `password`

### Scenario 2: View Dashboard

1. **Login berhasil** → Tampil Dashboard Tab
2. **Check elements:**
   - ✅ Welcome card dengan nama user
   - ✅ Role badge (DESIGNER/DEVELOPER)
   - ✅ 4 Statistics cards:
     - Total Tasks
     - In Progress
     - Completed
     - Overdue
   - ✅ Recent Tasks section (max 5)
3. **Pull to refresh** → Data updated

### Scenario 3: View My Tasks

1. **Tap "My Tasks" tab** di bottom navigation
2. **Expected:**
   - List semua task yang assigned
   - Setiap card menampilkan:
     - Task title
     - Priority badge
     - Status badge
     - Board & Project name
     - Due date (jika ada)
     - Subtask progress (jika ada)
3. **Tap Filter icon** → Pilih status filter
4. **Expected:** List filtered by status

### Scenario 4: View Task Detail

1. **Di My Tasks** → Tap salah satu task card
2. **Expected TaskDetailScreen dengan:**
   - Task title & priority
   - Project & Board info
   - Due date (jika ada, dengan overdue indicator)
   - Description
   - Status selector (6 chips)
   - Subtasks checklist (jika ada)
   - Comments list
   - Add comment input di bottom

### Scenario 5: Update Task Status

1. **Di Task Detail** → Lihat Status section
2. **Tap salah satu status chip** (misal: In Progress)
3. **Expected:**
   - Status updated
   - Chip color changed
   - SnackBar "Status updated successfully"
   - Data auto-refreshed
4. **Back ke Task List** → Status sudah berubah

### Scenario 6: Toggle Subtask

1. **Di Task Detail** → Scroll ke Subtasks section
2. **Tap checkbox** salah satu subtask
3. **Expected:**
   - Checkbox toggled (✅ atau ☐)
   - Progress counter updated (e.g., 2/5 → 3/5)
   - Data auto-refreshed
4. **Tap lagi** → Back to unchecked

### Scenario 7: Add Comment

1. **Di Task Detail** → Scroll ke bottom
2. **Type comment** di input field: "Testing comment feature"
3. **Tap send button** (floating action button)
4. **Expected:**
   - Input field cleared
   - New comment muncul di list
   - SnackBar "Comment added"
   - Scroll auto ke bawah
   - Comment shows:
     - User avatar
     - User fullname
     - Timestamp
     - Comment text

### Scenario 8: View Profile

1. **Tap "Profile" tab** di bottom navigation
2. **Expected:**
   - Avatar dengan initial user
   - Full name
   - Username dengan @
   - Role badge
   - Account Information card:
     - Email
     - Username
     - Role
     - Status (ACTIVE/INACTIVE)
   - About button
   - Logout button

### Scenario 9: Logout

1. **Di Profile** → Tap "Logout" button
2. **Expected:** Confirmation dialog
3. **Tap "Logout"** di dialog
4. **Expected:**
   - Token cleared from storage
   - Navigate to LoginScreen
   - Cannot go back to home

### Scenario 10: Register New Account

1. **Di Login Screen** → Tap "Register"
2. **Input data:**
   - Full Name: `Test User`
   - Username: `testuser`
   - Email: `test@example.com`
   - Role: `Developer`
   - Password: `password`
   - Confirm Password: `password`
3. **Tap Register**
4. **Expected:**
   - Account created
   - Auto-login
   - Navigate to Dashboard
5. **Logout dan login lagi** dengan credential baru

## 🔍 Validation Tests

### Form Validation

**Login Form:**
- Empty username → Error: "Please enter your email or username"
- Empty password → Error: "Please enter your password"

**Register Form:**
- Empty fullname → Error: "Please enter your full name"
- Username < 3 chars → Error: "Username must be at least 3 characters"
- Invalid email → Error: "Please enter a valid email"
- Password < 6 chars → Error: "Password must be at least 6 characters"
- Passwords don't match → Error: "Passwords do not match"

### API Error Handling

**Wrong Credentials:**
- Input wrong password → SnackBar red: "Invalid credentials"

**Network Error:**
- Stop API server
- Try login → SnackBar: "Connection error: ..."
- Tap retry → Retry request

**Token Expiry:**
- Delete token manually (or wait expiry)
- Navigate to app → Auto redirect to login

## 📱 UI/UX Tests

### Responsiveness
- ✅ ScrollView works on all screens
- ✅ Keyboard doesn't overlap input fields
- ✅ Pull to refresh works
- ✅ Loading indicators shown during API calls
- ✅ Cards properly sized on different screen sizes

### Navigation
- ✅ Bottom navigation works
- ✅ Back button works on detail screens
- ✅ Drawer/AppBar properly displayed
- ✅ Navigation persists selected tab

### Visual Elements
- ✅ Status colors match design (Grey, Blue, Orange, Purple, Teal, Green)
- ✅ Priority colors match design (Green, Orange, Red)
- ✅ Role badges colored correctly
- ✅ Overdue tasks shown in red
- ✅ Icons consistent throughout app

## 🐛 Edge Cases

### Empty States
- ✅ No tasks assigned → "No tasks assigned to you"
- ✅ No comments → "No comments yet"
- ✅ No subtasks → Section not shown
- ✅ Filter returns empty → "No tasks with status X"

### Data Loading
- ✅ Loading spinner during fetch
- ✅ Error message if fetch fails
- ✅ Retry button on error

### Concurrent Updates
- ✅ Multiple status updates → Queue properly
- ✅ Add comment while loading → Wait previous request

## 📊 Performance Tests

### Load Time
- First load < 3 seconds
- Task list load < 2 seconds
- Task detail load < 1 second

### Memory Usage
- No memory leaks on navigation
- Images properly cached
- API responses properly disposed

### Battery
- No background processes
- Efficient re-renders with Provider

## ✅ Checklist Before Demo

- [ ] API server running (`php artisan serve --port=8001`)
- [ ] Base URL configured correctly
- [ ] Flutter dependencies installed (`flutter pub get`)
- [ ] Test users exist in database
- [ ] Sample tasks assigned to test users
- [ ] Device/emulator running
- [ ] Internet/network connection works
- [ ] Hot reload enabled for quick fixes

## 🎯 Demo Flow

**Best demonstration order:**

1. **Show Login** → Login sebagai desainer
2. **Show Dashboard** → Explain statistics
3. **Show Task List** → Show filter feature
4. **Open Task Detail** → Show all features:
   - Update status
   - Toggle subtask
   - Add comment
5. **Show Profile** → Show user info
6. **Logout & Register** → Show register flow
7. **Login with new account** → Verify it works

## 🔧 Troubleshooting

### App doesn't start
```powershell
flutter clean
flutter pub get
flutter run
```

### Cannot connect to API
- Check API server: `curl http://localhost:8001/api/user`
- Check firewall settings
- Use correct IP address for physical device
- Ensure device and PC on same network

### White screen after login
- Check console for errors
- Verify API returns correct user data
- Check token is saved: Debug print in storage_service

### Lint errors
- Run `flutter pub get` first
- Restart VS Code
- Run `flutter analyze`

## 📸 Screenshots to Capture

For documentation:
1. Login Screen
2. Dashboard with stats
3. Task List
4. Task Detail (with subtasks & comments)
5. Status update (before & after)
6. Profile Screen
7. Register Screen

## 🎉 Success Criteria

App is ready to demo if:
- ✅ All 10 test scenarios pass
- ✅ No crashes or errors
- ✅ UI looks polished
- ✅ All features working
- ✅ Performance is smooth
- ✅ Error handling works

Good luck with your testing! 🚀
