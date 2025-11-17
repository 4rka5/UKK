<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementProjectBoard extends Model
{
    protected $table = 'boards';
    
    protected $fillable = [
        'project_id',
        'board_name',
        'description'
    ];

    /**
     * Relationship: Board belongs to a Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship: Board has many Cards
     */
    public function cards()
    {
        return $this->hasMany(ManagementProjectCard::class, 'board_id');
    }
}
