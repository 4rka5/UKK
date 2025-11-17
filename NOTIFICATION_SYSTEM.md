# Sistem Notifikasi Responsif

## Fitur Utama

### 1. **Real-time Updates**
- Auto-refresh setiap 10 detik (sebelumnya 30 detik)
- Toast notification untuk notifikasi baru
- Animasi bell yang berdering saat ada notifikasi baru
- Sound notification (opsional)

### 2. **Responsive Design**
- Dropdown notifikasi yang responsive untuk mobile
- Toast notifications yang mobile-friendly
- Smooth animations dan transitions
- Custom scrollbar untuk daftar notifikasi

### 3. **Tipe Notifikasi Berdasarkan Role**

#### Admin
- `project_update` - Update project
- `task_completed` - Task selesai dari member
- System notifications

#### Team Lead
- `task_completed` - Member menyelesaikan task
- `extension_request` - Permintaan perpanjangan deadline
- `blocker_reported` - Laporan hambatan
- `comment_added` - Komentar baru pada task

#### Developer / Designer
- `task_assigned` - Task baru ditugaskan
- `deadline_reminder` - Pengingat deadline
- `status_changed` - Status task berubah
- `comment_added` - Komentar baru

### 4. **Icon & Warna per Tipe**
- 📋 Task Assigned (Primary)
- ✅ Task Completed (Success)
- ⏰ Deadline Reminder (Warning)
- 💬 Comment Added (Info)
- 🔄 Status Changed (Secondary)
- 📁 Project Update (Dark)
- 📝 Extension Request (Warning)
- 🚧 Blocker Reported (Danger)

## Penggunaan

### Mengirim Notifikasi

```php
use App\Helpers\NotificationService;

// Task assigned
NotificationService::notifyTaskAssigned($user, $card, $assignedBy);

// Task completed
NotificationService::notifyTaskCompleted($card, $completedBy, $teamLead);

// Deadline reminder
NotificationService::notifyDeadlineReminder($user, $card, $daysLeft);

// Comment added
NotificationService::notifyCommentAdded($card, $commentedBy, $userIds);

// Status changed
NotificationService::notifyStatusChanged($card, $oldStatus, $newStatus, $changedBy, $userIds);

// Extension request
NotificationService::notifyExtensionRequest($card, $requester, $teamLead, $reason);

// Blocker reported
NotificationService::notifyBlockerReported($card, $reporter, $teamLead, $blockerDescription);

// Project update
NotificationService::notifyProjectUpdate($project, $updateMessage, $memberIds);

// Custom notification
NotificationService::send($users, $title, $message, $type, $data);
```

### Frontend Features

#### Auto-hide Alerts
Alert messages otomatis hilang setelah 5 detik

#### Toast Notifications
Toast muncul untuk notifikasi real-time dengan:
- Auto-hide setelah 5 detik
- Custom icon berdasarkan tipe
- Responsive di mobile

#### Notification Bell
- Badge dengan jumlah unread
- Animasi ring untuk notifikasi baru
- Pulse effect pada badge

## File-file Penting

### Backend
- `app/Helpers/NotificationService.php` - Service untuk mengirim notifikasi
- `app/Http/Controllers/NotificationController.php` - API endpoint notifikasi
- `app/Models/Notification.php` - Model notifikasi

### Frontend
- `resources/views/partials/notifications.blade.php` - Komponen notification bell
- `resources/views/includes/alerts.blade.php` - Alert messages
- `public/css/notifications.css` - Styling notifikasi

### Routes
```php
Route::middleware('auth')->group(function() {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount']);
    Route::get('/notifications/recent', [NotificationController::class, 'getRecent']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});
```

## Responsive Breakpoints

- **Desktop** (>768px): Full dropdown dengan width 380px
- **Mobile** (≤768px): Full-width dropdown dengan posisi fixed
- **Toast**: Min-width 300px, auto-adjust untuk mobile

## Performance

- Lazy loading untuk notifikasi lama
- Efficient caching dengan interval 10 detik
- Debounced scroll untuk loading infinite
- Optimized DOM updates

## Future Enhancements

1. Push notifications (via Service Workers)
2. Email notifications
3. Notification preferences per user
4. Group notifications
5. Notification categories filter
6. Mark as important/starred
7. Notification sound settings
8. Desktop notifications (browser permission)
