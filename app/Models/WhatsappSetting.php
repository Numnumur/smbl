<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    protected $fillable = [
        'admin_whatsapp_number',
        'fonnte_token'
    ];
}
