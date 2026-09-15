<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatKnowledge extends Model
{
    use HasFactory;

    protected $fillable = ['category', 'key', 'content', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];
}
