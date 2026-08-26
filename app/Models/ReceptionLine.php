<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceptionLine extends Model
{
    use SoftDeletes;

    protected $fillable = ['reception_id', 'purchase_line_id', 'quantity', 'unit_cost'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:4', 'unit_cost' => 'decimal:2'];
    }

    public function reception(): BelongsTo
    {
        return $this->belongsTo(Reception::class);
    }

    public function purchaseLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseLine::class);
    }
}
