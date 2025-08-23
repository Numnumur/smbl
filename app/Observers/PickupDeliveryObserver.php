<?php

namespace App\Observers;

use App\Models\PickupDelivery;
use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class PickupDeliveryObserver
{
    public function created(PickupDelivery $delivery): void
    {
        if ($delivery->whatsapp_notified_admin) return;

        $adminNumber = WhatsappSetting::first()?->admin_whatsapp_number;
        $token = WhatsappSetting::first()?->fonnte_token;

        if (!$adminNumber || !$token) {
            Log::error("WhatsApp admin/token tidak tersedia.");
            return;
        }

        $customer = $delivery->customer;
        $customerName = $customer->user->name;
        $date = Carbon::parse($delivery->date)->locale('id')->translatedFormat('l, j F Y');
        $time = Carbon::parse($delivery->time)->format('H:i');

        $message = implode("\n", [
            "~~ Sinar Laundry ~~",
            "",
            "*Ada permintaan antar jemput baru* dari pelanggan berikut",
            "Nama                         : {$customerName}",
            "Jenis Permintaan         : {$delivery->type}",
            "Hari dan Tanggal       : {$date}",
            "Pada Jam                    : {$time}",
            "Catatan Pelanggan     : {$delivery->customer_note}",
            "Alamat                        : {$customer->address}",
            "",
            "Silahkan lakukan konfirmasi lewat web."
        ]);

        $this->sendWhatsapp($adminNumber, $message, $token, function () use ($delivery) {
            $delivery->whatsapp_notified_admin = true;
            $delivery->saveQuietly();
        });
    }

    public function updated(PickupDelivery $delivery): void
    {
        if (
            !$delivery->isDirty('status') ||
            !in_array($delivery->status, ['Sudah Dikonfirmasi', 'Ditolak'])
        ) {
            return;
        }

        $token = WhatsappSetting::first()?->fonnte_token;

        $customer = $delivery->customer;
        $customerName = $customer->user->name;
        $date = Carbon::parse($delivery->date)->locale('id')->translatedFormat('l, j F Y');
        $time = Carbon::parse($delivery->time)->format('H:i');

        $messageHeader = [
            "~~ Sinar Laundry ~~",
            "",
            "*Permintaan Antar Jemput Anda*",
            "Atas Nama                  : {$customerName}",
            "Jenis Permintaan         : {$delivery->type}",
            "Hari dan Tanggal       : {$date}",
            "Pada Jam                    : {$time}",
            "Catatan Pelanggan     : {$delivery->customer_note}",
            "",
        ];

        if ($delivery->status === 'Sudah Dikonfirmasi') {
            $message = array_merge($messageHeader, [
                "✅ *Sudah dikonfirmasi oleh admin* dan akan dilakukan sesuai dengan waktu yang anda tentukan."
            ]);
        } elseif ($delivery->status === 'Ditolak') {
            $message = array_merge($messageHeader, [
                "❌ *Ditolak oleh admin*.",
                "Penyebab Penolakan: {$delivery->laundry_note}",
                "",
                "Dimohon pengertiannya, anda juga dapat mengajukan permintaan antar jemput lagi."
            ]);
        }

        $this->sendWhatsapp($customer->whatsapp, implode("\n", $message), $token, function () use ($delivery) {
            $delivery->whatsapp_notified_customer = true;
            $delivery->saveQuietly();
        });
    }

    private function sendWhatsapp(string $target, string $message, string $token, callable $onSuccess): void
    {
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
                    'target' => $target,
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
                Log::error("cURL error kirim WA: {$error}");
                return;
            }

            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] == true) {
                Log::info("WA sent to {$target}: {$response}");
                $onSuccess();
            } else {
                Log::warning("Fonnte gagal: {$response}");
            }
        } catch (\Throwable $e) {
            Log::error("Exception WA Fonnte: {$e->getMessage()}");
        }
    }
}
