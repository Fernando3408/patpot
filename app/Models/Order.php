<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = ['number', 'customer_id', 'store_id', 'ordered_on', 'delivery_on', 'notes', 'status'];

    protected function casts(): array
    {
        return ['ordered_on' => 'date', 'delivery_on' => 'date'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
