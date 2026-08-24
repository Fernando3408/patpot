<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseLine extends Model
{
    use SoftDeletes;

    protected $fillable = ['purchase_id', 'input_id', 'ordered_quantity', 'received_quantity', 'unit_cost'];

    protected function casts(): array
    {
        return ['ordered_quantity' => 'decimal:4', 'received_quantity' => 'decimal:4', 'unit_cost' => 'decimal:2'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class);
    }
}
