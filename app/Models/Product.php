<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'grams',
        'units_per_box',
        'stock_boxes',
        'min_stock_boxes',
        'sale_price_box',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'grams' => 'integer',
        'units_per_box' => 'integer',
        'stock_boxes' => 'integer',
        'min_stock_boxes' => 'integer',
        'sale_price_box' => 'decimal:2',
    ];

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function retail(): HasMany
    {
        return $this->hasMany(Retail::class);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function getCostPerBoxAttribute(): float
    {
        return round($this->recipes->sum(fn (Recipe $recipe): float => $recipe->input ? (float) $recipe->qty_per_box * (float) $recipe->input->unit_cost : 0), 2);
    }

    public function getProductionCapacityAttribute(): ?float
    {
        if ($this->recipes->isEmpty()) {
            return null;
        }

        $filtered = $this->recipes->filter(fn (Recipe $recipe) => $recipe->input && $recipe->qty_per_box > 0);
        if ($filtered->isEmpty()) {
            return null;
        }

        return floor($filtered->min(fn (Recipe $recipe): float => (float) $recipe->input->stock / (float) $recipe->qty_per_box));
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function delete(): bool
    {
        return parent::delete();
    }
}
