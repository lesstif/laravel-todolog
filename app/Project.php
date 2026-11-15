<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public  function user()
    {
        return $this->belongsTo(User::class);
    }

}
