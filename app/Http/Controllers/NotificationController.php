<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for current user
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications count (for AJAX)
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
            
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (for dropdown)
     */
    public function getRecent()
    {
        $userId = Auth::id();
        \Log::info('Getting notifications for user: ' . $userId);
        
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        \Log::info('Found notifications: ' . $notifications->count());
            
        return response()->json($notifications);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah ditandai sebagai dibaca'
        ], 200, [
            'Content-Type' => 'application/json'
        ]);
    }
    
    /**
     * Show notification detail and redirect to related page
     */
    public function show($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        // Mark as read
        $notification->markAsRead();
        
        // Redirect based on notification type and related resource
        if ($notification->related_type === 'Card' && $notification->related_id) {
            return redirect()->route('member.cards.show', $notification->related_id);
        } elseif ($notification->related_type === 'Project' && $notification->related_id) {
            // Redirect to project page based on user role
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->route('admin.projects.edit', $notification->related_id);
            } elseif ($user->role === 'team_lead') {
                return redirect()->route('lead.dashboard');
            } else {
                return redirect()->route('member.dashboard');
            }
        }
        
        // Default: redirect to notifications index
        return redirect()->route('notifications.index')
            ->with('status', 'Notifikasi tidak memiliki detail terkait');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return redirect()->back()->with('status', 'Semua notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        $notification->delete();
        
        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }
}
