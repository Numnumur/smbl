<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LoginPageImage extends Model
{
    protected $fillable = [
        'logo',
        'login_background'
    ];

    protected static function booted(): void
    {
        static::updating(function ($image) {
            $originalLogo = $image->getOriginal('logo');
            $originalLoginBackground = $image->getOriginal('login_background');

            if ($originalLogo && $image->isDirty('logo') && Storage::disk('public')->exists($originalLogo)) {
                Storage::disk('public')->delete($originalLogo);
            }

            if ($originalLoginBackground && $image->isDirty('login_background') && Storage::disk('public')->exists($originalLoginBackground)) {
                Storage::disk('public')->delete($originalLoginBackground);
            }
        });
    }
}
