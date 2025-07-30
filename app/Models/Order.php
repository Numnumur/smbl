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
        'type',
        'price',

        'discount_name',
        'discount_type',
        'discount_value',
        'total_price_before_discount',
        'total_price',

        'length',
        'width',
        'weight',
        'quantity',
        'customer_id',
        'laundry_note',
        'retrieval_proof',
        'delivery_proof',
        'whatsapp_notified',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
