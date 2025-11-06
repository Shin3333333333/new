<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'reminder_time',
        'user_id',
    ];

    protected $casts = [
        'reminder_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}