<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function show(ManagementProjectCard $card)
    {
        // Check if user is assigned to this card
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();

        if (!$isAssigned) {
            abort(403, 'Anda tidak memiliki akses ke card ini. Hanya tugas yang di-assign kepada Anda yang dapat diakses.');
        }

                // Load relationships
        $card->load([
            'project', 
            'creator', 
            'assignees', 
            'subtasks', 
            'comments.user'
        ]);

        return view('member.cards.show', compact('card'));
    }

    /**
     * Method ini di-disable karena member tidak boleh mengubah status card
     * Status card hanya bisa diubah oleh Team Lead melalui Lead\CardController
     * Member hanya bisa melihat card dan melaporkan progress melalui role-specific actions
     * 
     * @deprecated Not used - route removed from web.php
     */
    public function updateStatus(Request $request, ManagementProjectCard $card)
    {
        abort(403, 'Member tidak memiliki akses untuk mengubah status card. Hubungi Team Lead Anda.');
    }

    /**
     * Mulai timer - member klik tombol "Kerjakan"
     */
    public function startTimer(ManagementProjectCard $card)
    {
        $userId = Auth::id();
        
        // Cek jika tugas sudah selesai
        if ($card->status === 'done') {
            return back()->with('error', 'Tugas ini sudah selesai. Timer tidak dapat dimulai.');
        }
        
        // Check if user is assigned to this card
        $assignment = $card->assignees()->where('users.id', $userId)->first();
        
        if (!$assignment) {
            return back()->with('error', 'Anda tidak memiliki akses ke card ini.');
        }
        
        // Check if task is overdue and user cannot work
        if (!$card->canUserWork($userId)) {
            return back()->with('error', 'Tugas ini sudah melewati deadline. Silakan ajukan perpanjangan ke Team Lead.');
        }
        
        // Check if already working
        if ($assignment->pivot->is_working) {
            return back()->with('info', 'Timer sudah berjalan.');
        }
        
        // Check if user has another active timer
        $activeTimer = \DB::table('card_assignments')
            ->where('user_id', $userId)
            ->where('is_working', true)
            ->where('card_id', '!=', $card->id)
            ->first();
            
        if ($activeTimer) {
            return back()->with('error', 'Anda sudah memiliki timer aktif di card lain. Pause timer tersebut terlebih dahulu.');
        }
        
        // Start timer
        $card->assignees()->updateExistingPivot($userId, [
            'work_started_at' => now(),
            'is_working' => true,
            'assignment_status' => 'in_progress'
        ]);
        
        return back()->with('status', 'Timer dimulai! Selamat bekerja 💪');
    }

    /**
     * Pause timer - member pause pekerjaan
     */
    public function pauseTimer(ManagementProjectCard $card)
    {
        $userId = Auth::id();
        
        // Cek jika tugas sudah selesai
        if ($card->status === 'done') {
            return back()->with('error', 'Tugas ini sudah selesai. Timer tidak dapat di-pause.');
        }
        
        $assignment = $card->assignees()->where('users.id', $userId)->first();
        
        if (!$assignment || !$assignment->pivot->is_working) {
            return back()->with('error', 'Timer tidak sedang berjalan.');
        }
        
        $pivot = $assignment->pivot;
        
        // Calculate elapsed time
        $startTime = \Carbon\Carbon::parse($pivot->work_started_at);
        $elapsedSeconds = $startTime->diffInSeconds(now());
        
        // Update total work time
        $newTotal = $pivot->total_work_seconds + $elapsedSeconds;
        
        $card->assignees()->updateExistingPivot($userId, [
            'work_paused_at' => now(),
            'is_working' => false,
            'total_work_seconds' => $newTotal
        ]);
        
        $hours = floor($newTotal / 3600);
        $minutes = floor(($newTotal % 3600) / 60);
        
        return back()->with('status', "Timer di-pause. Total waktu kerja: {$hours} jam {$minutes} menit");
    }

    /**
     * Stop/Complete timer - member selesai mengerjakan
     */
    public function stopTimer(ManagementProjectCard $card)
    {
        $userId = Auth::id();
        
        // Cek jika tugas sudah selesai
        if ($card->status === 'done') {
            return back()->with('error', 'Tugas ini sudah selesai. Timer tidak dapat di-stop.');
        }
        
        $assignment = $card->assignees()->where('users.id', $userId)->first();
        
        if (!$assignment) {
            return back()->with('error', 'Anda tidak memiliki akses ke card ini.');
        }
        
        $pivot = $assignment->pivot;
        
        // If still working, pause first
        if ($pivot->is_working) {
            $startTime = \Carbon\Carbon::parse($pivot->work_started_at);
            $elapsedSeconds = $startTime->diffInSeconds(now());
            $newTotal = $pivot->total_work_seconds + $elapsedSeconds;
        } else {
            $newTotal = $pivot->total_work_seconds;
        }
        
        // Convert to hours for actual_hours
        $actualHours = round($newTotal / 3600, 2);
        
        // Update card actual hours
        $card->update([
            'actual_hours' => $actualHours
        ]);
        
        // Update assignment
        $card->assignees()->updateExistingPivot($userId, [
            'is_working' => false,
            'work_paused_at' => now(),
            'total_work_seconds' => $newTotal,
            'assignment_status' => 'completed'
        ]);
        
        return back()->with('status', "Pekerjaan selesai! Total: {$actualHours} jam. Timer direset.");
    }

    /**
     * Get current timer status
     */
    public function getTimerStatus(ManagementProjectCard $card)
    {
        $userId = Auth::id();
        
        $assignment = $card->assignees()->where('users.id', $userId)->first();
        
        if (!$assignment) {
            return response()->json(['error' => 'Not assigned'], 403);
        }
        
        $pivot = $assignment->pivot;
        $currentSeconds = $pivot->total_work_seconds;
        
        // If currently working, add elapsed time
        if ($pivot->is_working && $pivot->work_started_at) {
            $startTime = \Carbon\Carbon::parse($pivot->work_started_at);
            $currentSeconds += $startTime->diffInSeconds(now());
        }
        
        return response()->json([
            'is_working' => $pivot->is_working,
            'total_seconds' => $currentSeconds,
            'formatted_time' => $this->formatSeconds($currentSeconds)
        ]);
    }

    /**
     * Format seconds to HH:MM:SS
     */
    private function formatSeconds($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
    
    /**
     * Request extension for overdue task
     */
    public function requestExtension(Request $request, ManagementProjectCard $card)
    {
        $userId = Auth::id();
        
        $request->validate([
            'extension_reason' => ['required', 'string', 'max:500']
        ]);
        
        // Check if user is assigned
        $assignment = $card->assignees()->where('users.id', $userId)->first();
        
        if (!$assignment) {
            return back()->with('error', 'Anda tidak memiliki akses ke card ini.');
        }
        
        // Check if already requested
        if ($assignment->pivot->extension_requested && $assignment->pivot->extension_approved === null) {
            return back()->with('error', 'Anda sudah mengajukan perpanjangan. Tunggu approval dari Team Lead.');
        }
        
        // Update assignment
        $card->assignees()->updateExistingPivot($userId, [
            'extension_requested' => true,
            'extension_reason' => $request->extension_reason,
            'extension_requested_at' => now(),
            'extension_approved' => null,
            'extension_approved_by' => null,
            'extension_approved_at' => null
        ]);
        
        // Send notification to Team Lead
        $teamLead = $card->project->owner;
        if ($teamLead) {
            \App\Helpers\NotificationHelper::notifyExtensionRequested(
                $teamLead->id,
                $card->id,
                Auth::user()->fullname ?? Auth::user()->username,
                $card->card_title,
                $request->extension_reason
            );
        }
        
        return back()->with('status', 'Permohonan perpanjangan deadline telah dikirim ke Team Lead.');
    }
    
    public function addComment(Request $request, ManagementProjectCard $card)
    {
        $request->validate([
            'comment_text' => 'required|string|max:1000'
        ]);
        
        // Check if user is assigned to this card
        $userId = Auth::id();
        $isAssigned = $card->assignees()->where('users.id', $userId)->exists();
        
        if (!$isAssigned) {
            return back()->with('error', 'Anda tidak memiliki akses untuk berkomentar di card ini.');
        }
        
        $comment = \App\Models\ManagementProjectComment::create([
            'card_id' => $card->id,
            'user_id' => $userId,
            'comment_text' => $request->comment_text
        ]);
        
        // Send notification to all assignees except commenter and team lead
        $assignees = $card->assignees()->where('users.id', '!=', $userId)->get();
        $teamLeadMember = $card->project->members()->where('role', 'team_lead')->first();
        $commenter = Auth::user();
        
        foreach ($assignees as $assignee) {
            \App\Models\Notification::create([
                'user_id' => $assignee->id,
                'type' => 'comment',
                'title' => 'Komentar Baru di Card',
                'message' => $commenter->fullname . ' menambahkan komentar di card "' . $card->card_title . '"',
                'related_id' => $card->id,
                'related_type' => 'Card'
            ]);
        }
        
        // Send notification to team lead if exists and not the commenter
        if ($teamLeadMember && $teamLeadMember->user_id != $userId) {
            \App\Models\Notification::create([
                'user_id' => $teamLeadMember->user_id,
                'type' => 'comment',
                'title' => 'Komentar Baru di Card',
                'message' => $commenter->fullname . ' menambahkan komentar di card "' . $card->card_title . '"',
                'related_id' => $card->id,
                'related_type' => 'Card'
            ]);
        }
        
        return redirect()->back()->with('status', 'Komentar berhasil ditambahkan.');
    }
}
