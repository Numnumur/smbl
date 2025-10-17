<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\WhatsappSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (
            !$order->isDirty('status') ||
            !in_array($order->status, ['Selesai Diproses', 'Terkendala'])
        ) {
            return;
        }

        $customer = $order->customer;

        if (!$customer || !$customer->whatsapp) {
            Log::warning("WhatsApp tidak tersedia untuk customer ID: {$customer?->id}");
            $order->whatsapp_notified = false;
            $order->saveQuietly();
            return;
        }

        $token = WhatsappSetting::first()?->fonnte_token;

        if (!$token) {
            Log::error('Token Fonnte tidak tersedia di WhatsappSetting.');
            $order->whatsapp_notified = false;
            $order->saveQuietly();
            return;
        }

        $dateFormatted   = Carbon::parse($order->start_date)->translatedFormat('l, d F Y');
        $totalFormatted  = 'Rp. ' . number_format($order->total_price, 0, ',', '.');
        $customerName    = $customer->user->name;
        $isTerkendala    = $order->status === 'Terkendala';

        $lines = [
            "~~ Sinar Laundry ~~",
            "",
            "*Pesanan Anda:*",
            "Pada                : {$dateFormatted}",
            "Atas Nama      : {$customerName}",
            "Paket Pesanan : {$order->order_package}",
        ];

        if (!$isTerkendala) {
            $lines[] = "Biaya Pesanan : *{$totalFormatted}*";
        }

        if ($isTerkendala) {
            $lines[] = "*Sedang Terkendala ⚠️*";
            $lines[] = "";
            $lines[] = "Penyebab kendala: {$order->laundry_note}";
            $lines[] = "";
            $lines[] = "Mohon maaf atas ketidaknyamanannya.";
        } else {
            $lines[] = "*Telah Selesai ✅*";
            $lines[] = "";
            $lines[] = "Anda dapat mengajukan *pengantaran* atau mengambilnya secara langsung.";
        }

        $message = implode("\n", $lines);
        $target = $customer->whatsapp;

        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target'  => $target,
                    'message' => $message,
                ],
                CURLOPT_HTTPHEADER => [
                    "Authorization: {$token}",
                ],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Log::error("cURL error saat kirim ke Fonnte: {$error}");
                $order->whatsapp_notified = false;
            } else {
                $responseData = json_decode($response, true);

                if (isset($responseData['status']) && $responseData['status'] == true) {
                    $order->whatsapp_notified = true;
                } else {
                    Log::warning("Fonnte gagal merespon sukses: {$response}");
                    $order->whatsapp_notified = false;
                }

                Log::info("Fonnte response untuk Order ID {$order->id}: {$response}");
            }

            $order->saveQuietly();
        } catch (\Throwable $e) {
            Log::error("Exception saat kirim ke Fonnte: " . $e->getMessage());
            $order->whatsapp_notified = false;
            $order->saveQuietly();
        }
    }
}
