# Connection Error Troubleshooting

## Error: "Connection error: DioException [connection error]"

### Problem
Flutter app tidak bisa connect ke Laravel API server.

### Checklist Solutions

#### ✅ 1. Pastikan API Server Running

**Check:**
```powershell
# Di folder p2
cd c:\xampp\htdocs\UKK\p2
php artisan serve --port=8001
```

**Expected Output:**
```
Starting Laravel development server: http://127.0.0.1:8001
```

**Test API:**
```powershell
Invoke-WebRequest -Uri http://localhost:8001/api/login -Method POST -Headers @{"Accept"="application/json"; "Content-Type"="application/json"} -Body '{"login":"desainer","password":"password"}'
```

Should return status 200 with user data.

---

#### ✅ 2. Configure Correct Base URL

**File:** `lib/services/api_service.dart`

**Choose based on your device:**

**A. Android Emulator** (Default)
```dart
static const String baseUrl = 'http://10.0.2.2:8001/api';
```

**B. Physical Android Device**
1. Get your PC IP address:
```powershell
ipconfig
# Look for: IPv4 Address under "Wireless LAN adapter Wi-Fi" or "Ethernet"
# Example: 192.168.1.100
```

2. Update base URL:
```dart
static const String baseUrl = 'http://192.168.1.100:8001/api'; // Ganti dengan IP Anda
```

3. Make sure phone and PC on same WiFi network

**C. iOS Simulator**
```dart
static const String baseUrl = 'http://localhost:8001/api';
```

---

#### ✅ 3. Restart API Server with Host Binding

For physical devices, server must listen on all interfaces:

```powershell
cd c:\xampp\htdocs\UKK\p2
php artisan serve --host=0.0.0.0 --port=8001
```

Now accessible from:
- PC: http://localhost:8001
- Emulator: http://10.0.2.2:8001
- Physical Device: http://192.168.1.100:8001 (your PC IP)

---

#### ✅ 4. Check Firewall

**Windows Firewall might block incoming connections:**

1. Open Windows Firewall settings
2. Click "Allow an app through firewall"
3. Find "PHP" or allow port 8001
4. OR temporarily disable firewall for testing

---

#### ✅ 5. Hot Restart Flutter App

After changing base URL:

```bash
# In Flutter app
flutter run

# Or if already running:
# Press 'R' in terminal for hot restart
# Or 'r' for hot reload
```

**Full restart:**
```bash
flutter clean
flutter pub get
flutter run
```

---

#### ✅ 6. Test Connection from Device/Emulator

**Method 1: Browser Test**
- Open Chrome/Safari on emulator/device
- Navigate to: `http://10.0.2.2:8001/api/user` (or your IP)
- Should see: `{"message":"Unauthenticated."}`
- This means API is reachable ✅

**Method 2: Ping Test**
```bash
# From emulator terminal (ADB)
adb shell
ping 10.0.2.2
```

---

### Quick Fix Summary

```powershell
# Terminal 1: Start API with host binding
cd c:\xampp\htdocs\UKK\p2
php artisan serve --host=0.0.0.0 --port=8001

# Terminal 2: Check your IP (for physical device)
ipconfig
# Note your IPv4 address: 192.168.1.xxx

# Terminal 3: Update Flutter and run
cd c:\xampp\htdocs\UKK\flutter_app

# Edit lib/services/api_service.dart:
# - For Emulator: use http://10.0.2.2:8001/api
# - For Device: use http://192.168.1.xxx:8001/api (your IP)

flutter run
```

---

### Device-Specific URLs

| Device Type | Base URL | Notes |
|------------|----------|-------|
| Android Emulator | `http://10.0.2.2:8001/api` | Default, works out of box |
| iOS Simulator | `http://localhost:8001/api` | Same as Mac host |
| Physical Android | `http://192.168.1.100:8001/api` | Replace with your PC IP |
| Physical iOS | `http://192.168.1.100:8001/api` | Replace with your PC IP |

---

### Error Messages Explained

**"Connection timeout"**
- API server not running
- Wrong IP address
- Firewall blocking

**"Connection error"**
- Base URL incorrect
- Device not on same network (physical device)
- API server not bound to 0.0.0.0

**"401 Unauthorized"**
- ✅ API reachable!
- Login endpoint working
- Just need valid credentials

**"Cannot connect to server"**
- API server not running on port 8001
- Check with: `netstat -ano | findstr :8001`

---

### Testing Checklist

- [ ] API server running on port 8001
- [ ] API accessible from browser (show 401 or API response)
- [ ] Base URL configured correctly in `api_service.dart`
- [ ] Device and PC on same network (for physical device)
- [ ] Firewall not blocking port 8001
- [ ] Flutter app hot restarted after URL change
- [ ] Test credentials exist in database

---

### Still Not Working?

**Debug Steps:**

1. **Check API is actually running:**
```powershell
netstat -ano | findstr :8001
```
Should show LISTENING

2. **Check from Flutter app logs:**
- Look for full error message
- Check actual URL being called
- Verify request payload

3. **Try different base URL:**
```dart
// Try this for testing (Windows-specific)
static const String baseUrl = 'http://10.0.2.2:8001/api'; // Emulator
// OR
static const String baseUrl = 'http://127.0.0.1:8001/api'; // Localhost
```

4. **Enable Dio logging:**
```dart
// In ApiService constructor
_dio.interceptors.add(LogInterceptor(
  requestBody: true,
  responseBody: true,
  error: true,
));
```

---

### Success Criteria

✅ Login screen loads
✅ Can type credentials
✅ Tap login shows loading indicator
✅ Either:
   - Navigate to dashboard (success)
   - Show error message with specific details (not "connection error")

---

## Quick Start (TL;DR)

```powershell
# 1. Start API
cd c:\xampp\htdocs\UKK\p2
php artisan serve --host=0.0.0.0 --port=8001

# 2. Run Flutter (new terminal)
cd c:\xampp\htdocs\UKK\flutter_app
flutter run

# 3. Test login with:
Username: desainer
Password: password
```

If connection error persists, check base URL in `lib/services/api_service.dart` matches your device type!
