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
        'start_date',
        'end_date',
        'order_package_id',
    ];

    public function orderPackage(): BelongsTo
    {
        return $this->belongsTo(OrderPackage::class);
    }

    protected static function booted(): void
    {
        static::created(function (Discount $discount) {
            $valueFormatted = $discount->type === 'Langsung'
                ? 'Rp ' . number_format($discount->value, 0, ',', '.')
                : $discount->value . '%';

            $start = \Carbon\Carbon::parse($discount->start_date)->translatedFormat('j F Y');
            $end = \Carbon\Carbon::parse($discount->end_date)->translatedFormat('j F Y');

            $message = implode("\n", [
                "📢 *Promo Baru dari Sinar Laundry!*",
                "",
                "*{$discount->name}*",
                "Diskon: {$valueFormatted}",
                "Paket Pesanan: " . ($discount->orderPackage?->name ?? '-'),
                "Berlaku: {$start} s/d {$end}",
                "",
                "Jangan sampai terlewat! 💨",
            ]);

            WhatsappBroadcast::create([
                'title' => 'Promo Baru: ' . $discount->name,
                'message_content' => $message,
                'send_date' => null,
                'whatsapp_notified' => false,
                'recipient_count' => null,
            ]);
        });
    }
}
