<?php

namespace Database\Seeders;

use App\Models\OrderPackage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderPackages = [
            ['name' => 'Reguler Cuci Setrika 3 hari', 'type' => 'Kiloan', 'price' => 7000],
            ['name' => 'Reguler Cuci Setrika 2 hari', 'type' => 'Kiloan', 'price' => 8000],
            ['name' => 'Ekspress Cuci Setrika 1 hari', 'type' => 'Kiloan', 'price' => 10000],
            ['name' => 'Reguler Cuci Setrika Lainnya', 'type' => 'Kiloan', 'price' => 10000],
            ['name' => 'Reguler Cuci Lipat Lainnya', 'type' => 'Kiloan', 'price' => 8000],
            ['name' => 'Reguler Cuci Lipat 3 hari', 'type' => 'Kiloan', 'price' => 6000],
            ['name' => 'Reguler Cuci Lipat 2 hari', 'type' => 'Kiloan', 'price' => 7000],
            ['name' => 'Ekspress Cuci Lipat 1 hari', 'type' => 'Kiloan', 'price' => 8000],
            ['name' => 'Reguler Setrika 3 hari', 'type' => 'Kiloan', 'price' => 5000],
            ['name' => 'Reguler Setrika 2 hari', 'type' => 'Kiloan', 'price' => 6000],
            ['name' => 'Ekspress Setrika 1 hari', 'type' => 'Kiloan', 'price' => 7000],
            ['name' => 'Karpet Standar', 'type' => 'Karpet', 'price' => 10000],
            ['name' => 'Karpet Tebal', 'type' => 'Karpet', 'price' => 15000],
            ['name' => 'Ambal', 'type' => 'Lembaran', 'price' => 30000],
            ['name' => 'Ambal Besar', 'type' => 'Lembaran', 'price' => 50000],
            ['name' => 'Selimut', 'type' => 'Lembaran', 'price' => 10000],
            ['name' => 'Seprai', 'type' => 'Lembaran', 'price' => 10000],
            ['name' => 'Bedcover Kecil', 'type' => 'Lembaran', 'price' => 25000],
            ['name' => 'Bedcover Sedang', 'type' => 'Lembaran', 'price' => 30000],
            ['name' => 'Bedcover Besar', 'type' => 'Lembaran', 'price' => 35000],
            ['name' => 'Boneka Kecil', 'type' => 'Satuan', 'price' => 5000],
            ['name' => 'Boneka Sedang', 'type' => 'Satuan', 'price' => 15000],
            ['name' => 'Boneka Besar', 'type' => 'Satuan', 'price' => 25000],
            ['name' => 'Bantal Standar', 'type' => 'Satuan', 'price' => 10000],
            ['name' => 'Bantal Besar', 'type' => 'Satuan', 'price' => 15000],
            ['name' => 'Kasur Lipat', 'type' => 'Satuan', 'price' => 25000],
        ];

        foreach ($orderPackages as $op) {
            OrderPackage::create($op);
        }
    }
}
