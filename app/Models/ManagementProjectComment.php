<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementProjectComment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'card_id',
        'subtask_id',
        'user_id',
        'comment_text',
        'comment_type'
    ];

    public function card()
    {
        return $this->belongsTo(ManagementProjectCard::class, 'card_id');
    }

    public function subtask()
    {
        return $this->belongsTo(ManagementProjectSubtask::class, 'subtask_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
