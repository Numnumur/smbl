<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappVerification;
use App\Models\WhatsappSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class WhatsappVerificationController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'whatsapp' => ['required', 'string'],
        ]);

        $code = random_int(100000, 999999); // 6 digit kode
        $whatsapp = preg_replace('/[^0-9]/', '', $request->whatsapp);

        $expiresAt = now()->addMinute();

        WhatsappVerification::updateOrCreate(
            ['whatsapp' => $whatsapp],
            [
                'code' => $code,
                'expires_at' => $expiresAt,
                'verified' => false,
            ]
        );

        $token = WhatsappSetting::first()?->fonnte_token;

        if (!$token) {
            return response()->json(['message' => 'Token Fonnte tidak ditemukan.'], 500);
        }

        $message = "*Kode Verifikasi Sinar Laundry:*\n\n`{$code}`\n\nKode ini berlaku selama 1 menit.";

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $whatsapp,
            'message' => $message,
        ]);

        if ($response->successful()) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim pesan.'], 500);
        }
    }
}
