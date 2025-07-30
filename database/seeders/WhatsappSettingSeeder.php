<?php

namespace Database\Seeders;

use App\Models\WhatsappSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WhatsappSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WhatsappSetting::create([
            'admin_whatsapp_number' => '6285161248062',
            'fonnte_token' => 'x23rfBtjgipCVjNaoGCx',
        ]);
    }
}
