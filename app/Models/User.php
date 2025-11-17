<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['username','fullname','email','password','role','status'];
    protected $hidden = ['password','remember_token'];

    public function projectMemberships(): HasMany {
        return $this->hasMany(ProjectMember::class);
    }
    public function assignedCards() {
        return $this->belongsToMany(ManagementProjectCard::class, 'card_assignments', 'user_id', 'card_id')
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
    
    public function notifications(): HasMany {
        return $this->hasMany(Notification::class);
    }
    
    public function unreadNotifications() {
        return $this->notifications()->unread();
    }
    
    //public function timeLogs(): HasMany {
        //return $this->hasMany(TimeLog::class);
    //}

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isLead(): bool { return $this->role === 'team_lead'; }
    public function isDesigner(): bool { return $this->role === 'designer'; }
    public function isDeveloper(): bool { return $this->role === 'developer'; }

    /**
     * Check if user has any assigned tasks based on their role
     * - Admin: Always returns true (no task check needed)
     * - Team Lead: Checks if user has created projects (created_by in projects table)
     * - Members (Designer/Developer): Checks if user has cards assigned (not done status)
     * 
     * @return bool
     */
    public function hasTasks(): bool
    {
        // Admin tidak perlu dicek
        if ($this->isAdmin()) {
            return true;
        }

        // Team Lead: cek dari tabel projects berdasarkan created_by
        if ($this->isLead()) {
            return Project::where('created_by', $this->id)->exists();
        }

        // Members (Designer/Developer): cek dari tabel cards yang belum selesai
        // Hanya hitung card yang statusnya BUKAN 'done'
        return $this->assignedCards()
            ->where('status', '!=', 'done')
            ->exists();
    }

    /**
     * Get count of tasks assigned to user based on their role
     * - Admin: Returns 0
     * - Team Lead: Count of created projects
     * - Members: Count of assigned cards (not done)
     * 
     * @return int
     */
    public function getTasksCount(): int
    {
        if ($this->isAdmin()) {
            return 0;
        }

        if ($this->isLead()) {
            return Project::where('created_by', $this->id)->count();
        }

        // Members: hitung card yang statusnya BUKAN 'done'
        return $this->assignedCards()
            ->where('status', '!=', 'done')
            ->count();
    }

    /**
     * Get all tasks assigned to user based on their role
     * - Admin: Returns empty collection
     * - Team Lead: Returns created projects
     * - Members: Returns assigned cards (not done)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasks()
    {
        if ($this->isAdmin()) {
            return collect([]);
        }

        if ($this->isLead()) {
            return Project::where('created_by', $this->id)->get();
        }

        // Members: hanya card yang belum selesai
        return $this->assignedCards()
            ->where('status', '!=', 'done')
            ->get();
    }
}
