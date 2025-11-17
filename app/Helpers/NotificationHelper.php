<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    /**
     * Create a new notification
     */
    public static function create($userId, $type, $title, $message, $relatedId = null, $relatedType = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_id' => $relatedId,
            'related_type' => $relatedType,
            'is_read' => false
        ]);
    }

    /**
     * Notify team lead about task submission
     */
    public static function notifyTaskSubmitted($teamLeadId, $cardId, $submitterName, $cardTitle)
    {
        return self::create(
            $teamLeadId,
            'task_submitted',
            '✅ Task Progress Submitted',
            "{$submitterName} telah submit progress untuk task '{$cardTitle}' dan menunggu review Anda.",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Notify team lead about blocker report
     */
    public static function notifyBlockerReported($teamLeadId, $cardId, $reporterName, $cardTitle)
    {
        return self::create(
            $teamLeadId,
            'blocker_reported',
            '🚫 Blocker Reported - Urgent!',
            "{$reporterName} melaporkan kendala/blocker pada task '{$cardTitle}'. Priority telah diubah menjadi HIGH. Segera tindak lanjuti!",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Notify team lead about new project assignment
     */
    public static function notifyProjectAssigned($teamLeadId, $projectId, $projectName)
    {
        return self::create(
            $teamLeadId,
            'project_assigned',
            '📁 New Project Assigned',
            "Anda telah ditugaskan sebagai Team Lead untuk project '{$projectName}'.",
            $projectId,
            'Project'
        );
    }

    /**
     * Notify user about task assignment
     */
    public static function notifyTaskAssigned($userId, $cardId, $cardTitle, $boardName)
    {
        return self::create(
            $userId,
            'task_assigned',
            '📋 New Task Assigned',
            "Anda telah ditugaskan untuk mengerjakan task '{$cardTitle}' di board '{$boardName}'.",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Notify user about task approval
     */
    public static function notifyTaskApproved($userId, $cardId, $cardTitle)
    {
        return self::create(
            $userId,
            'task_approved',
            '✅ Task Approved',
            "Selamat! Task '{$cardTitle}' telah disetujui oleh Team Lead. Status: DONE.",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Notify user about task rejection
     */
    public static function notifyTaskRejected($userId, $cardId, $cardTitle, $reason)
    {
        return self::create(
            $userId,
            'task_rejected',
            '❌ Task Rejected',
            "Task '{$cardTitle}' ditolak oleh Team Lead. Alasan: {$reason}. Silakan perbaiki dan submit ulang.",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Notify team lead about extension request
     */
    public static function notifyExtensionRequested($teamLeadId, $cardId, $requesterName, $cardTitle, $reason)
    {
        return self::create(
            $teamLeadId,
            'extension_requested',
            '⏰ Extension Request - Deadline Overdue',
            "{$requesterName} meminta perpanjangan deadline untuk task '{$cardTitle}'. Alasan: {$reason}",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Notify user about extension approval
     */
    public static function notifyExtensionApproved($userId, $cardId, $cardTitle)
    {
        return self::create(
            $userId,
            'extension_approved',
            '✅ Extension Approved',
            "Perpanjangan deadline untuk task '{$cardTitle}' telah disetujui. Anda dapat melanjutkan pekerjaan.",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Notify user about extension rejection
     */
    public static function notifyExtensionRejected($userId, $cardId, $cardTitle, $reason)
    {
        return self::create(
            $userId,
            'extension_rejected',
            '❌ Extension Rejected',
            "Perpanjangan deadline untuk task '{$cardTitle}' ditolak. Alasan: {$reason}",
            $cardId,
            'ManagementProjectCard'
        );
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get unread count for a user
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get recent notifications for a user
     */
    public static function getRecent($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
