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
            // ----------- 1
            ['name' => 'Fadil', 'whatsapp' => null],

            // ----------- 2
            ['name' => 'Jamal', 'whatsapp' => null],

            // ----------- 3
            ['name' => 'Daniel', 'whatsapp' => null],

            // ----------- 4
            ['name' => 'Bu Ema', 'whatsapp' => null],

            // ----------- 5
            ['name' => 'Ambar', 'whatsapp' => null],

            // ----------- 6
            ['name' => 'Sella', 'whatsapp' => null],

            // ----------- 7
            ['name' => 'Asri', 'whatsapp' => null],

            // ----------- 8
            ['name' => 'Samuel', 'whatsapp' => null],

            // ----------- 9
            ['name' => 'Putri', 'whatsapp' => null],

            // ----------- 10
            ['name' => 'Ari', 'whatsapp' => null],

            // ----------- 11
            ['name' => 'Hendri', 'whatsapp' => null],

            // ----------- 12
            ['name' => 'Johan', 'whatsapp' => null],

            // ----------- 13
            ['name' => 'MH', 'whatsapp' => null],

            // ----------- 14
            ['name' => 'Eka', 'whatsapp' => null],

            // ----------- 15
            ['name' => 'Hera', 'whatsapp' => null],

            // ----------- 16
            ['name' => 'Pak Slamet', 'whatsapp' => null],

            // ----------- 17
            ['name' => 'Pak Wilman', 'whatsapp' => null],

            // ----------- 18
            ['name' => 'Fatih', 'whatsapp' => null],

            // ----------- 19
            ['name' => 'Deswita', 'whatsapp' => null],

            // ----------- 20
            ['name' => 'Mama Fajar', 'whatsapp' => null],

            // ----------- 21
            ['name' => 'Pak Eko', 'whatsapp' => null],

            // ----------- 22
            ['name' => 'Dio', 'whatsapp' => null],

            // ----------- 23
            ['name' => 'Mama Syifa', 'whatsapp' => null],

            // ----------- 24
            ['name' => 'Mama Tri', 'whatsapp' => null],

            // ----------- 25
            ['name' => 'Yan/Dwi', 'whatsapp' => null],

            // ----------- 26
            ['name' => 'Eben', 'whatsapp' => null],

            // ----------- 27
            ['name' => 'Bu Jevin', 'whatsapp' => null],
        ];

        foreach ($customers as $c) {
            Customer::create($c);
        }
    }
}
