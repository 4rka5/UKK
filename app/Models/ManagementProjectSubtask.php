<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementProjectSubtask extends Model
{
    protected $table = 'subtasks';

    protected $fillable = [
        'card_id',
        'subtask_title',
        'description',
        'status',
        'estimated_hours'
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2'
    ];

    public function card()
    {
        return $this->belongsTo(ManagementProjectCard::class, 'card_id');
    }

    public function comments()
    {
        return $this->hasMany(ManagementProjectComment::class, 'subtask_id');
    }
}
