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
            ['name' => 'Diskon Agustus 2025 Reguler Cuci Setrika 3 hari', 'type' => 'Langsung', 'value' => 2000, 'start_date' => '2025-08-01', 'end_date' => '2025-08-31', 'order_package_id' => 1],
            ['name' => 'Diskon Agustus 2025 Reguler Cuci Setrika 2 hari', 'type' => 'Persentase', 'value' => 2, 'start_date' => '2025-08-01', 'end_date' => '2025-08-31', 'order_package_id' => 2],
        ];

        foreach ($discounts as $d) {
            Discount::create($d);
        }
    }
}
