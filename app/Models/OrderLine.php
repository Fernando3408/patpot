<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderLine extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_id', 'product_id', 'boxes', 'price_box', 'discount_pct', 'dispatched_boxes'];

    protected function casts(): array
    {
        return ['boxes' => 'integer', 'price_box' => 'decimal:2', 'discount_pct' => 'decimal:2', 'dispatched_boxes' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
