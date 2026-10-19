<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    public const VAT_RATE = 0.19;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'grams',
        'units_per_box',
        'stock_boxes',
        'min_stock_boxes',
        'production_cost',
        'status',
        'reference_image',
        'deleted_by',
    ];

    protected $casts = [
        'grams' => 'integer',
        'units_per_box' => 'integer',
        'stock_boxes' => 'integer',
        'min_stock_boxes' => 'integer',
        'production_cost' => 'decimal:2',
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
        if ($this->production_cost !== null) {
            return (float) $this->production_cost;
        }

        return round($this->recipes->sum(function (Recipe $recipe): float {
            if (! $recipe->input) return 0;
            return $recipe->input->isService() && $recipe->fixed_cost !== null
                ? (float) $recipe->fixed_cost
                : (float) $recipe->qty_per_box * (float) $recipe->input->unit_cost;
        }), 2);
    }

    public function getCostPerBoxWithVatAttribute(): float
    {
        return round($this->cost_per_box * (1 + self::VAT_RATE), 2);
    }

    public function getCostPerUnitAttribute(): ?float
    {
        return $this->units_per_box > 0 ? round($this->cost_per_box / $this->units_per_box, 2) : null;
    }

    public function getCostPerUnitWithVatAttribute(): ?float
    {
        return $this->cost_per_unit !== null ? round($this->cost_per_unit * (1 + self::VAT_RATE), 2) : null;
    }

    public function getProductionCapacityAttribute(): ?float
    {
        if ($this->recipes->isEmpty()) {
            return null;
        }

        $filtered = $this->recipes->filter(fn (Recipe $recipe) => $recipe->input?->isMaterial() && $recipe->qty_per_box > 0);
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
        $this->recipes()->delete();
        $this->prices()->delete();
        $this->retail()->delete();
        $this->productions()->delete();

        return parent::delete();
    }
}
