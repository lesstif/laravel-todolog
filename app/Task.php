<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['name', 'description', 'due_date', 'priority', 'status'];

    // 완료 기한
    protected $dates = ['due_date'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeDueInDays($query, $days)
    {
        $now = \Carbon\Carbon::now();

        return $query->where('due_date', '>', $now)
             ->where('due_date', '<', $now->copy()->addDays($days));
    }

}
