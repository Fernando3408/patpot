<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retail extends Model
{
    use SoftDeletes;

    protected $table = 'retail';

    protected $fillable = [
        'store_id',
        'product_id',
        'cataloged',
        'stock_units',
        'transit_units',
        'weekly_sales',
        'min_stock',
        'reorder_point',
        'deleted_by',
    ];

    protected $casts = [
        'cataloged' => 'boolean',
        'stock_units' => 'decimal:2',
        'transit_units' => 'decimal:2',
        'weekly_sales' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'reorder_point' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // 1. QUIEBRE: Stock <= 0 y Tránsito <= 0
    public function getIsBreakAttribute(): bool
    {
        return $this->cataloged
            && (float) $this->stock_units <= 0
            && (float) $this->transit_units <= 0;
    }

    // 2. EN TRÁNSITO: Stock <= 0 pero Tránsito > 0
    public function getIsInTransitAttribute(): bool
    {
        return $this->cataloged
            && (float) $this->stock_units <= 0
            && (float) $this->transit_units > 0;
    }

    // 3. ATENCIÓN: Stock > 0 pero menor al stock mínimo
    public function getIsWarningAttribute(): bool
    {
        return $this->cataloged
            && (float) $this->stock_units > 0
            && (float) $this->stock_units < (float) $this->min_stock;
    }

    public function getCoverageWeeksAttribute(): ?float
    {
        if ((float) $this->weekly_sales <= 0) {
            return null;
        }

        return round((float) $this->stock_units / (float) $this->weekly_sales, 2);
    }

    public function getSuggestedReplenishmentUnitsAttribute(): float
    {
        $projected = (float) $this->stock_units + (float) $this->transit_units;
        $needed = (float) $this->min_stock - $projected;

        return $needed > 0 ? round($needed, 2) : 0;
    }

    public function getSuggestedReplenishmentBoxesAttribute(): int
    {
        $unitsPerBox = $this->product?->units_per_box ?: 1;

        return (int) ceil($this->suggested_replenishment_units / $unitsPerBox);
    }
}
