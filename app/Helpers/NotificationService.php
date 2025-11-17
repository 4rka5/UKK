<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send notification to user(s)
     * 
     * @param mixed $users - User model, user ID, or array of user IDs
     * @param string $title
     * @param string $message
     * @param string $type
     * @param array $data - Additional data (optional)
     * @return void
     */
    public static function send($users, string $title, string $message, string $type = 'default', array $data = [])
    {
        // Normalize users to array of user IDs
        $userIds = self::normalizeUsers($users);
        
        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => !empty($data) ? json_encode($data) : null,
                'is_read' => false,
            ]);
        }
    }
    
    /**
     * Admin notifications
     */
    public static function notifyAdmins(string $title, string $message, string $type = 'project_update')
    {
        $admins = User::where('role', 'admin')->pluck('id')->toArray();
        self::send($admins, $title, $message, $type);
    }
    
    /**
     * Team Lead notifications
     */
    public static function notifyTeamLead(int $teamLeadId, string $title, string $message, string $type = 'task_update')
    {
        self::send($teamLeadId, $title, $message, $type);
    }
    
    /**
     * Developer/Designer notifications
     */
    public static function notifyMember(int $memberId, string $title, string $message, string $type = 'task_assigned')
    {
        self::send($memberId, $title, $message, $type);
    }
    
    /**
     * Task assignment notification
     */
    public static function notifyTaskAssigned(User $assignee, $card, User $assignedBy)
    {
        $roleIcon = self::getRoleIcon($assignee->role);
        $title = "{$roleIcon} Tugas Baru Ditugaskan";
        $message = "Anda telah ditugaskan untuk: {$card->card_title}";
        
        self::send($assignee->id, $title, $message, 'task_assigned', [
            'card_id' => $card->id,
            'assigned_by' => $assignedBy->fullname ?? $assignedBy->username,
        ]);
    }
    
    /**
     * Task completed notification
     */
    public static function notifyTaskCompleted($card, User $completedBy, User $teamLead)
    {
        $title = "✅ Tugas Selesai";
        $message = "{$completedBy->fullname} telah menyelesaikan: {$card->card_title}";
        
        self::send($teamLead->id, $title, $message, 'task_completed', [
            'card_id' => $card->id,
            'completed_by' => $completedBy->id,
        ]);
    }
    
    /**
     * Deadline reminder notification
     */
    public static function notifyDeadlineReminder(User $user, $card, int $daysLeft)
    {
        $urgency = $daysLeft <= 1 ? '🚨' : '⏰';
        $title = "{$urgency} Pengingat Deadline";
        $message = "Tugas '{$card->card_title}' akan berakhir dalam {$daysLeft} hari";
        
        self::send($user->id, $title, $message, 'deadline_reminder', [
            'card_id' => $card->id,
            'days_left' => $daysLeft,
        ]);
    }
    
    /**
     * Comment added notification
     */
    public static function notifyCommentAdded($card, User $commentedBy, array $notifyUsers)
    {
        $title = "💬 Komentar Baru";
        $message = "{$commentedBy->fullname} mengomentari: {$card->card_title}";
        
        foreach ($notifyUsers as $userId) {
            // Don't notify the commenter
            if ($userId != $commentedBy->id) {
                self::send($userId, $title, $message, 'comment_added', [
                    'card_id' => $card->id,
                    'commented_by' => $commentedBy->id,
                ]);
            }
        }
    }
    
    /**
     * Status changed notification
     */
    public static function notifyStatusChanged($card, string $oldStatus, string $newStatus, User $changedBy, array $notifyUsers)
    {
        $title = "🔄 Status Berubah";
        $message = "Status '{$card->card_title}' diubah dari {$oldStatus} ke {$newStatus}";
        
        foreach ($notifyUsers as $userId) {
            if ($userId != $changedBy->id) {
                self::send($userId, $title, $message, 'status_changed', [
                    'card_id' => $card->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]);
            }
        }
    }
    
    /**
     * Extension request notification
     */
    public static function notifyExtensionRequest($card, User $requester, User $teamLead, string $reason)
    {
        $title = "📝 Permintaan Perpanjangan Deadline";
        $message = "{$requester->fullname} meminta perpanjangan untuk: {$card->card_title}";
        
        self::send($teamLead->id, $title, $message, 'extension_request', [
            'card_id' => $card->id,
            'requester' => $requester->id,
            'reason' => $reason,
        ]);
    }
    
    /**
     * Blocker reported notification
     */
    public static function notifyBlockerReported($card, User $reporter, User $teamLead, string $blockerDescription)
    {
        $title = "🚧 Hambatan Dilaporkan";
        $message = "{$reporter->fullname} melaporkan hambatan pada: {$card->card_title}";
        
        self::send($teamLead->id, $title, $message, 'blocker_reported', [
            'card_id' => $card->id,
            'reporter' => $reporter->id,
            'blocker' => $blockerDescription,
        ]);
    }
    
    /**
     * Project update notification
     */
    public static function notifyProjectUpdate($project, string $updateMessage, array $memberIds)
    {
        $title = "📁 Update Project";
        $message = "Project '{$project->project_name}': {$updateMessage}";
        
        self::send($memberIds, $title, $message, 'project_update', [
            'project_id' => $project->id,
        ]);
    }
    
    /**
     * Helper: Normalize users parameter to array of user IDs
     */
    private static function normalizeUsers($users): array
    {
        if ($users instanceof User) {
            return [$users->id];
        }
        
        if (is_numeric($users)) {
            return [(int) $users];
        }
        
        if (is_array($users)) {
            return array_map('intval', $users);
        }
        
        return [];
    }
    
    /**
     * Helper: Get role icon
     */
    private static function getRoleIcon(string $role): string
    {
        return match($role) {
            'admin' => '👑',
            'team_lead' => '👨‍💼',
            'developer' => '👨‍💻',
            'designer' => '🎨',
            default => '👤',
        };
    }
}
