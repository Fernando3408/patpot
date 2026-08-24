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
        'stock_units' => 'integer',
        'transit_units' => 'integer',
        'weekly_sales' => 'integer',
        'min_stock' => 'integer',
        'reorder_point' => 'integer',
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
            && (int) $this->stock_units <= 0
            && (int) $this->transit_units <= 0;
    }

    // 2. EN TRÁNSITO: Stock <= 0 pero Tránsito > 0
    public function getIsInTransitAttribute(): bool
    {
        return $this->cataloged
            && (int) $this->stock_units <= 0
            && (int) $this->transit_units > 0;
    }

    // 3. ATENCIÓN: Stock > 0 pero menor al stock mínimo
    public function getIsWarningAttribute(): bool
    {
        return $this->cataloged
            && (int) $this->stock_units > 0
            && (int) $this->stock_units < (int) $this->min_stock;
    }

    public function getCoverageWeeksAttribute(): ?float
    {
        if ((int) $this->weekly_sales <= 0) {
            return null;
        }

        return round((int) $this->stock_units / (int) $this->weekly_sales, 1);
    }

    public function getSuggestedReplenishmentUnitsAttribute(): int
    {
        $projected = (int) $this->stock_units + (int) $this->transit_units;
        $needed = (int) $this->min_stock - $projected;

        return $needed > 0 ? $needed : 0;
    }

    public function getSuggestedReplenishmentBoxesAttribute(): int
    {
        $unitsPerBox = $this->product?->units_per_box ?: 1;

        return (int) ceil($this->suggested_replenishment_units / $unitsPerBox);
    }
}
