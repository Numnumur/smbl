<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class ShowTime extends Command
{
    protected $signature = 'time:now';
    protected $description = 'Menampilkan waktu saat ini sesuai timezone Laravel';

    public function handle()
    {
        $now = Carbon::now(); // mengikuti timezone di config/app.php
        $this->info('Waktu saat ini: ' . $now->toDateTimeString());
    }
}
