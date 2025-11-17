<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementProjectCard extends Model
{
    protected $table = 'management_project_cards';
    protected $fillable = [
        'project_id','card_title','description','created_by','assigned_to','due_date',
        'status','priority','estimated_hours','actual_hours'
    ];

    /**
     * Get the project this card belongs to
     */
    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function subtasks() {
        return $this->hasMany(ManagementProjectSubtask::class, 'card_id');
    }

    public function assignees() {
        return $this->belongsToMany(User::class, 'card_assignments', 'card_id', 'user_id')
                    ->withPivot([
                        'assignment_status', 
                        'assigned_at', 
                        'work_started_at', 
                        'work_paused_at', 
                        'total_work_seconds', 
                        'is_working',
                        'extension_requested',
                        'extension_reason',
                        'extension_requested_at',
                        'extension_approved',
                        'extension_approved_by',
                        'extension_approved_at'
                    ]);
    }

    public function comments() {
        return $this->hasMany(ManagementProjectComment::class, 'card_id');
    }
    
    /**
     * Check if card is overdue
     */
    public function isOverdue()
    {
        if (!$this->due_date || $this->status === 'done') {
            return false;
        }
        
        return \Carbon\Carbon::parse($this->due_date)->isPast();
    }
    
    /**
     * Check if user can work on this card (not overdue or has extension approved)
     */
    public function canUserWork($userId)
    {
        // Tidak bisa bekerja jika card sudah review atau done
        if (in_array($this->status, ['review', 'done'])) {
            return false;
        }
        
        if (!$this->isOverdue()) {
            return true;
        }
        
        // Check if user has approved extension
        $assignment = $this->assignees()->where('users.id', $userId)->first();
        if ($assignment && $assignment->pivot->extension_approved === true) {
            return true;
        }
        
        return false;
    }
}
