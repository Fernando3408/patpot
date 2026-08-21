<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Input extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'stock',
        'safety_stock',
        'weekly_consumption',
        'lead_time_days',
        'target_weeks',
        'min_purchase',
        'purchase_multiple',
        'unit_cost',
        'transit',
        'supplier_id',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'weekly_consumption' => 'decimal:2',
        'target_weeks' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'purchase_multiple' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'transit' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function purchaseLines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function getCoverageWeeksAttribute(): ?float
    {
        if ((float) $this->weekly_consumption <= 0) {
            return null;
        }

        return round((float) $this->stock / (float) $this->weekly_consumption, 2);
    }

    public function getCoverageDaysAttribute(): ?float
    {
        $daily = (float) $this->weekly_consumption / 7;
        if ($daily <= 0) {
            return null;
        }

        return round((float) $this->stock / $daily, 0);
    }

    public function getReorderPointAttribute(): float
    {
        $dailyConsumption = (float) $this->weekly_consumption / 7;

        return round(($dailyConsumption * (float) $this->lead_time_days) + (float) $this->safety_stock, 2);
    }

    public function getProjectedStockAttribute(): float
    {
        return round((float) $this->stock + (float) $this->transit - $this->committed_quantity, 2);
    }

    public function getCommittedQuantityAttribute(): float
    {
        $recipes = $this->relationLoaded('recipes')
            ? $this->recipes
            : $this->recipes()->with('product.productions')->get();

        return round($recipes->sum(function (Recipe $recipe): float {
            $productions = $recipe->product?->productions ?? collect();

            return $productions
                ->whereIn('status', ['planned', 'in_progress'])
                ->sum('planned_boxes') * (float) $recipe->qty_per_box;
        }), 2);
    }

    public function getInventoryLevelAttribute(): string
    {
        $projected = $this->projected_stock;

        if ($projected <= 0) {
            return 'critico';
        }

        if ($projected <= $this->reorder_point) {
            return 'atencion';
        }

        return 'ok';
    }

    public function getSuggestedPurchaseAttribute(): float
    {
        $targetStock = (float) $this->weekly_consumption * (float) $this->target_weeks;
        $needed = $targetStock - $this->projected_stock;

        if ($needed <= 0) {
            return 0;
        }

        return $this->roundPurchase($needed);
    }

    protected function roundPurchase(float $qty): float
    {
        $minPurchase = (float) $this->min_purchase;
        $multiple = (float) $this->purchase_multiple ?: 1;

        $qty = max($qty, $minPurchase);

        return ceil($qty / $multiple) * $multiple;
    }

    public function formattedQuantity(float|int|string $quantity): string
    {
        if ($this->usesWholeQuantities()) {
            return number_format((int) round((float) $quantity), 0, ',', '.');
        }

        return number_format((float) $quantity, 2, ',', '.');
    }

    public function formattedStock(): string
    {
        return $this->formattedQuantity($this->stock);
    }

    public function formattedTransit(): string
    {
        return $this->formattedQuantity($this->transit);
    }

    public function formattedSafetyStock(): string
    {
        return $this->formattedQuantity($this->safety_stock);
    }

    public function formattedWeeklyConsumption(): string
    {
        return $this->formattedQuantity($this->weekly_consumption);
    }

    public function formattedProjectedStock(): string
    {
        return $this->formattedQuantity($this->projected_stock);
    }

    private function usesWholeQuantities(): bool
    {
        $unit = mb_strtolower($this->unit);

        return str_contains($unit, 'kg') || str_contains($unit, 'caja');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function delete(): bool
    {
        $this->recipes()->delete();
        $this->purchaseLines()->delete();

        return parent::delete();
    }
}
