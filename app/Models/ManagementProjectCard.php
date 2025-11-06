<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementProjectCard extends Model
{
    protected $table = 'cards';
    protected $fillable = [
        'board_id','card_title','description','created_by','assigned_to','due_date',
        'status','priority','estimated_hours','actual_hours'
    ];

    public function board() {
        return $this->belongsTo(ManagementProjectBoard::class, 'board_id');
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
                    ->withTimestamps()
                    ->withPivot('assignment_status', 'assigned_at');
    }

    public function comments() {
        return $this->hasMany(ManagementProjectComment::class, 'card_id');
    }
}
