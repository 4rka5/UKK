<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementProjectCardAssignment extends Model
{
    protected $table = 'card_assignments';
    
    protected $fillable = [
        'card_id',
        'user_id',
        'assigned_at',
        'assignment_status',
        'work_started_at',
        'work_paused_at',
        'total_work_seconds',
        'is_working'
    ];
    
    protected $casts = [
        'assigned_at' => 'datetime',
        'work_started_at' => 'datetime',
        'work_paused_at' => 'datetime',
        'is_working' => 'boolean',
    ];
    
    public $timestamps = false;
    
    public function card()
    {
        return $this->belongsTo(ManagementProjectCard::class, 'card_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
