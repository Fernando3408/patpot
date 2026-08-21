<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'input_id',
        'qty_per_box',
    ];

    protected $casts = [
        'qty_per_box' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class);
    }
}
