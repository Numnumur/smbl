<?php

namespace Database\Seeders;

use App\Models\Localization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingLocalizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Localization::create([
            'timezone' => 'Asia/Makassar',
        ]);
    }
}
