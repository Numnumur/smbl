<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderPackage extends Model
{
    protected $fillable = [
        'name',
        'type',
        'price',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class, 'order_package_id');
    }
}
