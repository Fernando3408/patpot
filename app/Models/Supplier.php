<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'rut',
        'contact_name',
        'email',
        'phone',
        'lead_time_days',
        'payment_terms',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function inputs(): HasMany
    {
        return $this->hasMany(Input::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function delete(): bool
    {
        $this->purchases()->each(function (Purchase $purchase) {
            $purchase->lines()->delete();
            $purchase->delete();
        });
        $this->inputs()->delete();

        return parent::delete();
    }
}
