<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'business_name',
        'trade_name',
        'rut',
        'type',
        'channel',
        'contact',
        'email',
        'payment_terms',
        'discount',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
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
        $this->stores()->each(fn (Store $s) => $s->delete());
        $this->prices()->delete();
        $this->orders()->each(function (Order $order) {
            $order->lines()->delete();
            $order->shipments()->each(fn (Shipment $s) => $s->lines()->delete());
            $order->shipments()->delete();
            $order->delete();
        });

        return parent::delete();
    }
}
