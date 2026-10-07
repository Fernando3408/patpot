<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderInputConsumption extends Model
{
    protected $fillable = ['order_id', 'order_line_id', 'input_id', 'boxes', 'qty_per_box', 'quantity', 'reversed_at', 'user_id'];

    protected function casts(): array
    {
        return ['boxes' => 'decimal:4', 'qty_per_box' => 'decimal:4', 'quantity' => 'decimal:4', 'reversed_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class);
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
