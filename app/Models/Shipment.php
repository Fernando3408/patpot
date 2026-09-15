<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_id', 'shipped_on', 'total', 'freight_cost', 'management_cost', 'other_cost'];

    protected function casts(): array
    {
        return [
            'shipped_on' => 'date',
            'total' => 'decimal:2',
            'freight_cost' => 'decimal:2',
            'management_cost' => 'decimal:2',
            'other_cost' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ShipmentLine::class);
    }
}
