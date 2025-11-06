<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementProjectBoard extends Model
{
    protected $table = 'boards';
    protected $fillable = ['project_id','board_name','description'];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function cards() {
        return $this->hasMany(ManagementProjectCard::class, 'board_id');
    }
}

?>
