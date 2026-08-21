<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentLine extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_line_id', 'boxes', 'price_box'];

    protected function casts(): array
    {
        return ['boxes' => 'integer', 'price_box' => 'decimal:2'];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class);
    }
}
