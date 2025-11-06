# Flutter App - Error Fixes Log

## Errors Fixed (November 6, 2025)

### 1. API Service Fixes

**File:** `lib/services/api_service.dart`

#### Fix 1: getMyCards() - Added status filter parameter
```dart
// Before
Future<Map<String, dynamic>> getMyCards() async

// After  
Future<Map<String, dynamic>> getMyCards({String? status}) async
```
**Reason:** TaskProvider needs to pass status filter to API.

#### Fix 2: getCardDetail() - Simplified parameters
```dart
// Before
Future<Map<String, dynamic>> getCardDetail(int boardId, int cardId) async

// After
Future<Map<String, dynamic>> getCardDetail(int cardId) async  
```
**Reason:** API endpoint changed to `/cards/{id}` instead of `/boards/{boardId}/cards/{cardId}`.

#### Fix 3: updateSubtask() - Simplified parameters
```dart
// Before
Future<Map<String, dynamic>> updateSubtask(
  int cardId,
  int subtaskId,
  Map<String, dynamic> data,
) async

// After
Future<Map<String, dynamic>> updateSubtask(
  int subtaskId,
  String status,
) async
```
**Reason:** API endpoint uses `/subtasks/{id}` directly, only needs status parameter.

---

### 2. Main App Fix

**File:** `lib/main.dart`

#### Fix: CardTheme type mismatch
```dart
// Before
cardTheme: CardTheme(
  elevation: 2,
  shape: RoundedRectangleBorder(
    borderRadius: BorderRadius.circular(12),
  ),
),

// After
cardTheme: const CardThemeData(
  elevation: 2,
  shape: RoundedRectangleBorder(
    borderRadius: BorderRadius.all(Radius.circular(12)),
  ),
),
```
**Reason:** ThemeData expects `CardThemeData` not `CardTheme`, and const optimization.

---

### 3. Home Screen Fixes

**File:** `lib/screens/home/home_screen.dart`

#### Fix 1: Task title getter
```dart
// Before
task.title

// After
task.taskTitle
```
**Reason:** Model uses `taskTitle` getter (alias for `cardTitle`).

#### Fix 2: Null-safe board name
```dart
// Before
Text(task.board.name)

// After
Text(task.board?.name ?? 'No Board')
```
**Reason:** Board can be null, need null-safe access.

#### Fix 3: Due date display method
```dart
// Before
task.dueDateFormatted

// After
task.dueDateDisplay
```
**Reason:** Use `dueDateDisplay` for relative dates (Today, Tomorrow, X days).

#### Fix 4: Tab switching from child widget
```dart
// Added in _HomeScreenState
void switchToTab(int index) {
  _onItemTapped(index);
}

// In "View All" button
final homeScreenState = context.findAncestorStateOfType<_HomeScreenState>();
if (homeScreenState != null) {
  homeScreenState.switchToTab(1);
}
```
**Reason:** Can't call setState from outside StatefulWidget, need public method.

---

### 4. Task Detail Screen Fix

**File:** `lib/screens/tasks/task_detail_screen.dart`

#### Fix: Removed unused import
```dart
// Removed
import '../../models/task_card.dart';
```
**Reason:** TaskCard model already imported via provider, not needed directly.

---

## Verification

All errors verified with `get_errors()` tool:
- ✅ lib/main.dart - No errors
- ✅ lib/providers/task_provider.dart - No errors
- ✅ lib/screens/home/home_screen.dart - No errors
- ✅ lib/screens/tasks/task_detail_screen.dart - No errors
- ✅ lib/services/api_service.dart - No errors

---

## Next Steps

1. **Run flutter pub get** to install dependencies:
   ```bash
   cd c:\xampp\htdocs\UKK\flutter_app
   flutter pub get
   ```

2. **Start API server**:
   ```bash
   cd c:\xampp\htdocs\UKK\p2
   php artisan serve --port=8001
   ```

3. **Run the app**:
   ```bash
   cd c:\xampp\htdocs\UKK\flutter_app
   flutter run
   ```

4. **Test with credentials**:
   - Username: `desainer` or `developer`
   - Password: `password`

---

## Status

**✅ ALL ERRORS FIXED - APP READY TO RUN**

Last Updated: November 6, 2025
