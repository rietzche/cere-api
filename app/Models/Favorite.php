<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'course_id', 'user_id'
    ];

    function course()
    {
        return $this->belongsTo(Course::class);
    }

    function user()
    {
        return $this->belongsTo(User::class);
    }
}
