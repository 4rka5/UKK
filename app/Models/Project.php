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
     * Check if project can be submitted for approval
     */
    public function canSubmitForApproval()
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    /**
     * Check if project is pending approval
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if project is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }
}
