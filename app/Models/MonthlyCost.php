<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MonthlyCost extends Model
{
    protected $fillable = ['cost_on', 'concept', 'category', 'amount', 'type', 'recurring', 'notes', 'user_id'];

    protected function casts(): array
    {
        return ['cost_on' => 'date', 'amount' => 'decimal:2', 'recurring' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
