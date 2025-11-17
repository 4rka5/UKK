<!-- Notification Bell Component -->
<style>
.notification-bell {
    position: relative;
    transition: transform 0.2s;
}
.notification-bell:hover {
    transform: scale(1.1);
}
.notification-bell.has-new {
    animation: ring 2s ease-in-out infinite;
}
@keyframes ring {
    0%, 100% { transform: rotate(0deg); }
    10%, 30% { transform: rotate(-10deg); }
    20%, 40% { transform: rotate(10deg); }
}
.notification-dropdown {
    min-width: 380px;
    max-width: 95vw;
    max-height: 600px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15) !important;
}
@media (max-width: 576px) {
    .notification-dropdown {
        min-width: 100vw;
        right: -15px !important;
        left: auto !important;
    }
}
.notif-item {
    padding: 12px 16px;
    border-left: 3px solid transparent;
    transition: all 0.2s;
    cursor: pointer;
}
.notif-item:hover {
    background-color: #f8f9fa !important;
}
.notif-item.unread {
    background-color: #e7f3ff;
    border-left-color: #0d6efd;
}
.notif-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
</style>

<div class="dropdown">
    <a class="nav-link position-relative notification-bell" href="#" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell fs-5"></i>
        @php
            $unreadCount = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
        @endphp
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notif-badge" style="display: {{ $unreadCount > 0 ? 'inline' : 'none' }};">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
        <div class="dropdown-header d-flex justify-content-between align-items-center py-3 px-3">
            <span class="fw-bold">🔔 Notifikasi</span>
            <div class="d-flex gap-2">
                <form action="{{ route('notifications.readAll') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0" title="Tandai semua sudah dibaca">
                        <i class="bi bi-check-all"></i>
                    </button>
                </form>
                <a href="{{ route('notifications.index') }}" class="btn btn-link btn-sm text-decoration-none p-0" title="Lihat semua">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
        </div>
        <div class="dropdown-divider m-0"></div>
        
        <div id="notification-list" style="max-height: 450px; overflow-y: auto;">
            @php
                $recentNotifications = \App\Models\Notification::where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            @endphp
            
            @if($recentNotifications->count() > 0)
                @foreach($recentNotifications as $notif)
                    <div class="notif-item {{ $notif->is_read ? '' : 'unread' }}" onclick="markAsRead({{ $notif->id }})">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="notif-icon 
                                @if($notif->type === 'comment' || $notif->type === 'comment_added') bg-info text-white
                                @elseif($notif->type === 'task_assigned') bg-primary text-white
                                @elseif($notif->type === 'task_completed') bg-success text-white
                                @elseif($notif->type === 'deadline_reminder') bg-warning text-dark
                                @elseif($notif->type === 'blocker_reported') bg-danger text-white
                                @else bg-light text-dark
                                @endif
                            ">
                                @if($notif->type === 'comment' || $notif->type === 'comment_added')
                                    💬
                                @elseif($notif->type === 'task_assigned')
                                    📋
                                @elseif($notif->type === 'task_completed')
                                    ✅
                                @elseif($notif->type === 'deadline_reminder')
                                    ⏰
                                @elseif($notif->type === 'blocker_reported')
                                    🚧
                                @else
                                    🔔
                                @endif
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong class="small">{{ $notif->title }}</strong>
                                    @if(!$notif->is_read)
                                        <span class="badge bg-primary badge-sm">Baru</span>
                                    @endif
                                </div>
                                <p class="small text-muted mb-1">{{ $notif->message }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $notif->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-bell-slash fs-3"></i>
                    <p class="mb-0 mt-2 small">Tidak ada notifikasi baru</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Toast Container for Real-time Notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
        <div class="toast-header">
            <span id="toast-icon" class="me-2"></span>
            <strong class="me-auto" id="toast-title">Notifikasi Baru</strong>
            <small id="toast-time">Baru saja</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toast-message">
            <!-- Message will be inserted here -->
        </div>
    </div>
</div>

<script>
/* Cache Buster: {{ time() }} */
let lastNotificationCount = 0;
let previousNotificationIds = new Set();

// Load notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    
    // Refresh every 10 seconds for better responsiveness
    setInterval(loadNotifications, 10000);
});

function loadNotifications() {
    console.log('Loading notifications...');
    
    // Get unread count
    fetch('/notifications/count')
        .then(response => {
            console.log('Count response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Notification count data:', data);
            const badge = document.getElementById('notif-badge');
            const bell = document.querySelector('.notification-bell');
            
            console.log('Badge element:', badge);
            console.log('Bell element:', bell);
            
            if (!badge || !bell) {
                console.error('Badge or bell element not found!');
                return;
            }
            
            if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'inline';
                bell.classList.add('has-new');
                console.log('Badge updated to:', badge.textContent);
                
                // Show toast if new notification arrived
                if (data.count > lastNotificationCount && lastNotificationCount > 0) {
                    playNotificationSound();
                }
            } else {
                badge.style.display = 'none';
                bell.classList.remove('has-new');
                console.log('No unread notifications, badge hidden');
            }
            
            lastNotificationCount = data.count;
        })
        .catch(error => {
            console.error('Error loading notification count:', error);
        });
    
    // Get recent notifications
    fetch('/notifications/recent')
        .then(response => {
            console.log('Recent response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Recent notifications raw data:', data);
            console.log('Is array?', Array.isArray(data));
            console.log('Length:', data.length);
            
            const listContainer = document.getElementById('notification-list');
            
            if (!listContainer) {
                console.error('notification-list element not found!');
                return;
            }
            
            // Ensure data is array
            if (!Array.isArray(data)) {
                console.error('Data is not an array:', typeof data);
                data = [];
            }
            
            // Check for new notifications
            const currentIds = new Set(data.map(n => n.id));
            const newNotifications = data.filter(n => !previousNotificationIds.has(n.id) && !n.is_read);
            
            if (newNotifications.length > 0 && previousNotificationIds.size > 0) {
                // Show toast for newest notification
                showToast(newNotifications[0]);
            }
            
            previousNotificationIds = currentIds;
            
            if (data.length === 0) {
                console.log('No notifications, showing empty state');
                listContainer.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-bell-slash fs-3"></i>
                        <p class="mb-0 mt-2 small">Tidak ada notifikasi baru</p>
                    </div>
                `;
                return;
            }
            
            console.log('Rendering', data.length, 'notifications');
            
            listContainer.innerHTML = data.map((notif, index) => {
                console.log('Rendering notif', index, ':', notif);
                const icon = getNotificationIcon(notif.type);
                const bgColor = getNotificationColor(notif.type);
                
                return `
                    <div class="notif-item ${notif.is_read ? '' : 'unread'}" onclick="markAsRead(${notif.id})">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="notif-icon ${bgColor}">
                                ${icon}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong class="small">${escapeHtml(notif.title)}</strong>
                                    ${!notif.is_read ? '<span class="badge bg-primary badge-sm">Baru</span>' : ''}
                                </div>
                                <p class="small text-muted mb-1">${escapeHtml(notif.message)}</p>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> ${timeAgo(notif.created_at)}
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            console.log('Notifications rendered successfully, total:', data.length);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            const listContainer = document.getElementById('notification-list');
            if (listContainer) {
                listContainer.innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle fs-3"></i>
                        <p class="mb-0 mt-2 small">Gagal memuat notifikasi</p>
                        <p class="mb-0 small">${error.message}</p>
                    </div>
                `;
            }
        });
}

function getNotificationIcon(type) {
    const icons = {
        'task_assigned': '📋',
        'task_completed': '✅',
        'deadline_reminder': '⏰',
        'comment': '💬',
        'comment_added': '💬',
        'status_changed': '🔄',
        'project_update': '📁',
        'extension_request': '📝',
        'blocker_reported': '🚧',
        'default': '🔔'
    };
    return icons[type] || icons.default;
}

function getNotificationColor(type) {
    const colors = {
        'task_assigned': 'bg-primary text-white',
        'task_completed': 'bg-success text-white',
        'deadline_reminder': 'bg-warning text-dark',
        'comment': 'bg-info text-white',
        'comment_added': 'bg-info text-white',
        'status_changed': 'bg-secondary text-white',
        'project_update': 'bg-dark text-white',
        'extension_request': 'bg-warning text-dark',
        'blocker_reported': 'bg-danger text-white',
        'default': 'bg-light text-dark'
    };
    return colors[type] || colors.default;
}

function showToast(notification) {
    const toast = document.getElementById('liveToast');
    const toastIcon = document.getElementById('toast-icon');
    const toastTitle = document.getElementById('toast-title');
    const toastMessage = document.getElementById('toast-message');
    const toastTime = document.getElementById('toast-time');
    
    if (!toast) return;
    
    toastIcon.textContent = getNotificationIcon(notification.type);
    toastTitle.textContent = notification.title;
    toastMessage.textContent = notification.message;
    toastTime.textContent = 'Baru saja';
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

function playNotificationSound() {
    // Optional: play a subtle notification sound
    // You can add an audio element or use Web Audio API
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSqDzvLZiTYIFmS37OihUBELUKvm');
        audio.volume = 0.3;
        audio.play().catch(() => {}); // Ignore if autoplay is blocked
    } catch (e) {
        // Silent fail
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function markAsRead(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

function markAllAsRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            loadNotifications();
            // Hide badge
            const badge = document.getElementById('notif-badge');
            if (badge) {
                badge.style.display = 'none';
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Baru saja';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} menit lalu`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam lalu`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)} hari lalu`;
    return date.toLocaleDateString('id-ID');
}
</script>
