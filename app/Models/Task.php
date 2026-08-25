<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'owner', 'due_on', 'priority', 'module', 'status', 'notes', 'completed_on'];

    protected function casts(): array
    {
        return ['due_on' => 'date', 'completed_on' => 'date'];
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_on->isPast();
    }
}
