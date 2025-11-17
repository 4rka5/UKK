# Work Timer Feature - Tracking Waktu Kerja Member

## Overview
Fitur timer otomatis yang berjalan ketika member mengklik tombol "Kerjakan" untuk melacak waktu kerja secara akurat.

## Fitur Utama

### 1. **Start Timer** ▶️
- Member klik tombol "Mulai Kerjakan"
- Timer mulai berjalan secara real-time
- Status assignment berubah menjadi `in_progress`
- Timestamp `work_started_at` dicatat

### 2. **Pause Timer** ⏸️
- Member bisa pause pekerjaan kapan saja
- Waktu yang sudah berjalan di-accumulate ke `total_work_seconds`
- Timer berhenti tapi tidak direset
- Bisa di-resume dengan klik "Mulai Kerjakan" lagi

### 3. **Stop Timer** ⏹️
- Member klik "Selesai & Catat Waktu" ketika pekerjaan selesai
- Total waktu kerja dihitung dan disimpan ke `actual_hours` di card
- Status assignment berubah menjadi `completed`
- Timer direset

## Database Schema

### Tabel: `card_assignments`

```sql
-- Kolom baru untuk timer
work_started_at     TIMESTAMP NULL     -- Waktu mulai bekerja
work_paused_at      TIMESTAMP NULL     -- Waktu pause terakhir
total_work_seconds  INTEGER DEFAULT 0  -- Total detik bekerja (accumulated)
is_working          BOOLEAN DEFAULT 0  -- Flag apakah sedang bekerja
```

## Workflow

```
┌─────────────────────────────────────────────────────────┐
│              WORK TIMER WORKFLOW                        │
└─────────────────────────────────────────────────────────┘

[Card Assigned to Member]
         │
         ↓
    ┌─────────┐
    │ IDLE    │ is_working = false, total_work_seconds = 0
    └─────────┘
         │
         │ Click "Mulai Kerjakan"
         ↓
    ┌─────────┐
    │ WORKING │ is_working = true, timer berjalan
    └─────────┘
         │
         ├─→ Click "Pause" ──→ Calculate elapsed time
         │                     Add to total_work_seconds
         │                     is_working = false
         │                     
         └─→ Click "Selesai" ──→ Calculate final time
                                  Update card.actual_hours
                                  assignment_status = completed
                                  Timer reset
```

## UI Components

### Timer Display
```
┌─────────────────────────────┐
│      ⏱️ Timer Pekerjaan      │
├─────────────────────────────┤
│                             │
│       02:35:17              │  ← Real-time counter
│       HH:MM:SS              │
│   ● Timer Berjalan          │  ← Status indicator
│                             │
│  [▶️ Mulai Kerjakan]        │
│  [⏸️ Pause]                 │
│  [⏹️ Selesai & Catat Waktu] │
│                             │
└─────────────────────────────┘
```

### Button States

| Button | Visible When | Action |
|--------|--------------|--------|
| ▶️ Mulai Kerjakan | Timer tidak berjalan | Start/Resume timer |
| ⏸️ Pause | Timer sedang berjalan | Pause timer |
| ⏹️ Selesai | Ada waktu tercatat | Stop & save time |

## Backend Logic

### Start Timer
```php
public function startTimer(ManagementProjectCard $card)
{
    // Validasi assignment
    // Check if already working
    // Check if user has another active timer
    
    $card->assignees()->updateExistingPivot($userId, [
        'work_started_at' => now(),
        'is_working' => true,
        'assignment_status' => 'in_progress'
    ]);
}
```

### Pause Timer
```php
public function pauseTimer(ManagementProjectCard $card)
{
    // Calculate elapsed time
    $startTime = Carbon::parse($pivot->work_started_at);
    $elapsedSeconds = $startTime->diffInSeconds(now());
    
    // Accumulate to total
    $newTotal = $pivot->total_work_seconds + $elapsedSeconds;
    
    $card->assignees()->updateExistingPivot($userId, [
        'work_paused_at' => now(),
        'is_working' => false,
        'total_work_seconds' => $newTotal
    ]);
}
```

### Stop Timer
```php
public function stopTimer(ManagementProjectCard $card)
{
    // Calculate final time
    // If still working, pause first
    
    // Convert to hours
    $actualHours = round($newTotal / 3600, 2);
    
    // Update card
    $card->update(['actual_hours' => $actualHours]);
    
    // Update assignment
    $card->assignees()->updateExistingPivot($userId, [
        'assignment_status' => 'completed'
    ]);
}
```

## Frontend - Real-time Update

### JavaScript Auto-refresh
```javascript
let timerInterval = setInterval(updateTimer, 1000);

function updateTimer() {
  fetch(`/member/cards/${cardId}/timer/status`)
    .then(response => response.json())
    .then(data => {
      document.getElementById('timer-display').textContent = data.formatted_time;
    });
}
```

### API Endpoint
```
GET /member/cards/{card}/timer/status

Response:
{
  "is_working": true,
  "total_seconds": 9317,
  "formatted_time": "02:35:17"
}
```

## Business Rules

### Rule 1: Single Active Timer
- Member hanya bisa punya 1 timer aktif dalam 1 waktu
- Jika ada timer aktif di card lain, tidak bisa start timer baru
- Harus pause timer yang aktif terlebih dahulu

```php
$activeTimer = DB::table('card_assignments')
    ->where('user_id', $userId)
    ->where('is_working', true)
    ->where('card_id', '!=', $card->id)
    ->first();
    
if ($activeTimer) {
    return back()->with('error', 'Timer lain masih aktif!');
}
```

### Rule 2: Time Accumulation
- Waktu kerja di-accumulate, bukan di-overwrite
- Member bisa start-pause berkali-kali
- Total waktu = sum of all work sessions

```php
$newTotal = $pivot->total_work_seconds + $elapsedSeconds;
```

### Rule 3: Auto Status Update
- Start timer → assignment_status = `in_progress`
- Stop timer → assignment_status = `completed`
- Pause → status tidak berubah

### Rule 4: Actual Hours Conversion
- Database: simpan dalam detik (integer)
- Card actual_hours: convert ke jam (float, 2 decimal)

```php
$actualHours = round($totalSeconds / 3600, 2);
// 9317 seconds = 2.59 hours
```

## Routes

```php
// Timer management
POST   /member/cards/{card}/timer/start   → startTimer()
POST   /member/cards/{card}/timer/pause   → pauseTimer()
POST   /member/cards/{card}/timer/stop    → stopTimer()
GET    /member/cards/{card}/timer/status  → getTimerStatus()
```

## Validation & Error Handling

### Validations
1. ✅ User must be assigned to card
2. ✅ Cannot start if already working
3. ✅ Cannot have multiple active timers
4. ✅ Cannot pause if not working
5. ✅ Cannot stop if no time recorded

### Error Messages
```php
// Not assigned
'Anda tidak memiliki akses ke card ini.'

// Already working
'Timer sudah berjalan.'

// Multiple timers
'Anda sudah memiliki timer aktif di card lain.'

// Not working
'Timer tidak sedang berjalan.'
```

## Benefits

### For Members
- ✅ **Tracking otomatis** - tidak perlu manual input jam
- ✅ **Akurat** - waktu tercatat persis
- ✅ **Flexible** - bisa pause-resume kapan saja
- ✅ **Visual feedback** - lihat waktu berjalan real-time

### For Team Leads
- ✅ **Data akurat** - actual hours terisi otomatis
- ✅ **Monitor progress** - lihat siapa yang sedang bekerja
- ✅ **Analytics** - data time tracking untuk reporting

### For Project
- ✅ **Transparency** - waktu kerja tercatat jelas
- ✅ **Billing accuracy** - untuk project yang charge by hour
- ✅ **Performance metrics** - estimate vs actual

## Testing Scenarios

### Test 1: Start Timer
```php
// Given: User assigned to card
// When: Click "Mulai Kerjakan"
// Then: is_working = true, work_started_at = now()
```

### Test 2: Pause Timer
```php
// Given: Timer running for 30 minutes
// When: Click "Pause"
// Then: total_work_seconds = 1800, is_working = false
```

### Test 3: Resume After Pause
```php
// Given: Timer paused at 30 minutes
// When: Click "Mulai Kerjakan" again, work 15 minutes, pause
// Then: total_work_seconds = 2700 (45 minutes)
```

### Test 4: Stop Timer
```php
// Given: Worked for 2.5 hours
// When: Click "Selesai"
// Then: card.actual_hours = 2.5, assignment_status = completed
```

### Test 5: Single Active Timer Rule
```php
// Given: User has active timer on Card A
// When: Try to start timer on Card B
// Then: Error - must pause Card A first
```

## UI Enhancements

### Visual Indicators
```css
/* Pulse animation for active timer */
.pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
```

### Timer Display
- Monospace font untuk angka
- Large display size (display-4)
- HH:MM:SS format
- Real-time update every second

## Future Enhancements

### Possible Improvements
1. **Timer History** - Log semua start/pause events
2. **Break Timer** - Separate timer untuk istirahat
3. **Productivity Stats** - Grafik waktu kerja per hari
4. **Notifications** - Alert jika lupa pause timer
5. **Overtime Warning** - Alert jika melebihi estimated hours
6. **Export Time Logs** - Download timesheet

## Summary

Fitur Work Timer memberikan cara profesional untuk tracking waktu kerja:

| Feature | Status |
|---------|--------|
| Start Timer | ✅ |
| Pause Timer | ✅ |
| Stop Timer | ✅ |
| Real-time Display | ✅ |
| Auto Save to Actual Hours | ✅ |
| Single Active Timer Rule | ✅ |
| Time Accumulation | ✅ |
| Mobile Responsive | ✅ |

**Next Step:** Member tinggal klik "Mulai Kerjakan" dan fokus bekerja, timer akan handle tracking waktu secara otomatis! ⏱️💪
