<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['project_name','description','deadline','created_by','assigned_to'];

    public function owner() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function boards() {
        return $this->hasMany(ManagementProjectBoard::class);
    }

    public function members() {
        return $this->hasMany(ProjectMember::class);
    }
}
