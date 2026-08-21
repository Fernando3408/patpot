<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use SoftDeletes;

    protected $fillable = ['number', 'product_id', 'planned_boxes', 'actual_boxes', 'status', 'planned_on', 'completed_on', 'notes'];

    protected function casts(): array
    {
        return ['planned_boxes' => 'integer', 'actual_boxes' => 'integer', 'planned_on' => 'date', 'completed_on' => 'date'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
