<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_name',
        'description',
        'deadline',
        'created_by',
        'assigned_to',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason'
    ];

    protected $casts = [
        'deadline' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function owner() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewer() {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function boards() {
        return $this->hasMany(ManagementProjectBoard::class);
    }

    public function members() {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * Check if project is active (sedang dikerjakan)
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if project is done (selesai)
     */
    public function isDone()
    {
        return $this->status === 'done';
    }
}
