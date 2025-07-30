<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupDelivery extends Model
{
    protected $fillable = [
        'date_and_time',
        'type',
        'status',
        'customer_note',
        'laundry_note',
        'customer_id',
        'whatsapp_notified_admin',
        'whatsapp_notified_customer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
