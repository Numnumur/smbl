<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappBroadcast extends Model
{
    protected $fillable = [
        'title',
        'message_content',
        'send_date',
        'whatsapp_notified',
        'recipient_count',
    ];
}
