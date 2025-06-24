<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'entry_date',
        'exit_date',
        'status',
        'order_package',
        'retrieval_proof',
        'delivery_proof',
        'type',
        'price',
        'total_price',
        'length',
        'width',
        'weight',
        'quantity',
        'customer_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderPackage(): BelongsTo
    {
        return $this->belongsTo(OrderPackage::class);
    }
}
