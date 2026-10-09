<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseOption extends Model
{
    protected $fillable = ['type', 'name', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
