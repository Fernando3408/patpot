<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'code',
        'name',
        'city',
        'region',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function retail(): HasMany
    {
        return $this->hasMany(Retail::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function delete(): bool
    {
        $this->retail()->delete();

        $orderIds = $this->orders()->pluck('id');
        $shipmentIds = \App\Models\Shipment::whereIn('order_id', $orderIds)->pluck('id');
        \App\Models\ShipmentLine::whereIn('shipment_id', $shipmentIds)->delete();
        \App\Models\Shipment::whereIn('order_id', $orderIds)->delete();
        \App\Models\OrderLine::whereIn('order_id', $orderIds)->delete();
        $this->orders()->delete();

        return parent::delete();
    }
}
