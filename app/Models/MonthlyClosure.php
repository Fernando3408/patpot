<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyClosure extends Model
{
    protected $fillable = ['month', 'sales', 'product_cost', 'expenses', 'monthly_costs', 'net_profit', 'closed_at', 'closed_by'];

    protected function casts(): array
    {
        return ['closed_at' => 'datetime', 'sales' => 'decimal:2', 'product_cost' => 'decimal:2', 'expenses' => 'decimal:2', 'monthly_costs' => 'decimal:2', 'net_profit' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
