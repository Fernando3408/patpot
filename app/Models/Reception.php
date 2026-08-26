<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reception extends Model
{
    use SoftDeletes;

    protected $fillable = ['purchase_id', 'received_on', 'total', 'user_id'];

    protected function casts(): array
    {
        return ['received_on' => 'date', 'total' => 'decimal:2'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReceptionLine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
