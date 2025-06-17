<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'value',
        'minimum',
        'start_date',
        'end_date',
        'order_package_id',
    ];

    public function orderPackage(): BelongsTo
    {
        return $this->belongsTo(OrderPackage::class);
    }
}
