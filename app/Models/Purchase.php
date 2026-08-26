<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = ['number', 'supplier_id', 'status', 'ordered_on', 'expected_on', 'notes'];

    protected function casts(): array
    {
        return ['ordered_on' => 'date', 'expected_on' => 'date'];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class);
    }
}
