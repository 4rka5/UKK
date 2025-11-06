<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = ['username','fullname','email','password','role','status'];
    protected $hidden = ['password','remember_token'];

    public function projectMemberships(): HasMany {
        return $this->hasMany(ProjectMember::class);
    }
    public function assignedCards() {
        return $this->belongsToMany(ManagementProjectCard::class, 'card_assignments', 'user_id', 'card_id')
                    ->withTimestamps()
                    ->withPivot('assignment_status', 'assigned_at');
    }
    //public function timeLogs(): HasMany {
        //return $this->hasMany(TimeLog::class);
    //}

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isLead(): bool { return $this->role === 'team_lead'; }
    public function isDesigner(): bool { return $this->role === 'designer'; }
    public function isDeveloper(): bool { return $this->role === 'developer'; }
}
