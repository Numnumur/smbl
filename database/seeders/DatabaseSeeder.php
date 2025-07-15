<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingLocalizationSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            OrderPackageSeeder::class,
            DiscountSeeder::class,
            OrderSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
