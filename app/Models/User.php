<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

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
     * - Team Lead: Checks if user has ACTIVE projects (status = 'active')
     * - Members (Designer/Developer): Checks if user is in ACTIVE projects (via project_members)
     * 
     * @return bool
     */
    public function hasTasks(): bool
    {
        // Admin tidak perlu dicek
        if ($this->isAdmin()) {
            return true;
        }

        // Team Lead: cek dari tabel projects yang AKTIF (status = 'active')
        if ($this->isLead()) {
            return Project::where('created_by', $this->id)
                ->where('status', 'active')
                ->exists();
        }

        // Members (Designer/Developer): cek dari project_members untuk project yang AKTIF
        return $this->projectMemberships()
            ->whereHas('project', function($query) {
                $query->where('status', 'active');
            })
            ->exists();
    }

    /**
     * Get count of tasks assigned to user based on their role
     * - Admin: Returns 0
     * - Team Lead: Count of ACTIVE projects only (status = 'active')
     * - Members: Count of ACTIVE project memberships only
     * 
     * @return int
     */
    public function getTasksCount(): int
    {
        if ($this->isAdmin()) {
            return 0;
        }

        if ($this->isLead()) {
            // Hanya hitung project yang AKTIF (status = 'active')
            return Project::where('created_by', $this->id)
                ->where('status', 'active')
                ->count();
        }

        // Members: hitung project membership yang projectnya AKTIF
        return $this->projectMemberships()
            ->whereHas('project', function($query) {
                $query->where('status', 'active');
            })
            ->count();
    }

    /**
     * Get all tasks assigned to user based on their role
     * - Admin: Returns empty collection
     * - Team Lead: Returns ACTIVE projects only
     * - Members: Returns ACTIVE project memberships
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasks()
    {
        if ($this->isAdmin()) {
            return collect([]);
        }

        if ($this->isLead()) {
            // Hanya return project yang AKTIF
            return Project::where('created_by', $this->id)
                ->where('status', 'active')
                ->get();
        }

        // Members: return project dari project_members yang AKTIF
        return $this->projectMemberships()
            ->with('project')
            ->whereHas('project', function($query) {
                $query->where('status', 'active');
            })
            ->get()
            ->pluck('project');
    }
}
