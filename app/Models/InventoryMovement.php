<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = ['input_id', 'product_id', 'kind', 'quantity', 'reference', 'notes', 'user_id'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:4'];
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
