<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendTestWhatsapp extends Command
{
    protected $signature = 'send:test-whatsapp {number} {--message=Hai! Ini tes dari Laravel 🚀}';
    protected $description = 'Kirim pesan WhatsApp melalui Fonnte API';

    public function handle()
    {
        $number = $this->argument('number');
        $message = $this->option('message');

        // Pastikan nomor dimulai dengan 62
        $target = preg_replace('/^0/', '62', $number);

        $this->info("Mengirim pesan ke: $target");
        $this->info("Pesan: $message");

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . env('FONNTE_TOKEN'), // Token dari .env
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $this->error("Curl Error: $err");
        } else {
            $this->info("Respon: $response");
        }
    }
}
