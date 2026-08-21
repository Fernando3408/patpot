<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'product_id',
        'price_box',
        'offer_price',
        'offer_until',
    ];

    protected $casts = [
        'price_box' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'offer_until' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePriceAttribute()
    {
        if (
            $this->offer_price !== null &&
            $this->offer_until !== null &&
            Carbon::parse($this->offer_until)->greaterThanOrEqualTo(Carbon::today())
        ) {
            return $this->offer_price;
        }

        return $this->price_box;
    }
}
