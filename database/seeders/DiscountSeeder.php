<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            ['name' => 'Diskon Juni 2025', 'type' => 'Persentase', 'value' => 5.5, 'minimum' => 10000, 'start_date' => '2025-06-01', 'end_date' => '2025-06-30', 'order_package_id' => 1],
            ['name' => 'Diskon Juni 2025', 'type' => 'Langsung', 'value' => 5000, 'minimum' => null, 'start_date' => '2025-06-01', 'end_date' => '2025-06-30', 'order_package_id' => 2],
        ];

        foreach ($discounts as $d) {
            Discount::create($d);
        }
    }
}
