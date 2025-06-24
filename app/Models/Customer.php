<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'whatsapp',
        'address',
        'note',
    ];

    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
