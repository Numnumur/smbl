<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            ['name' => 'Adi Nirmala', 'whatsapp' => '085610102020', 'address' => 'Jalan Satelit', 'note' => '-'],
            ['name' => 'Budi Setiadi', 'whatsapp' => '085187879010', 'address' => 'Jalan Sudirman', 'note' => '-'],
            ['name' => 'Udin', 'whatsapp' => '089664648765', 'address' => 'Gang Nanas', 'note' => '-'],
            ['name' => 'Putri', 'whatsapp' => '087844442222', 'address' => 'Gang Manggis', 'note' => '-'],
        ];

        foreach ($customers as $c) {
            Customer::create($c);
        }
    }
}
