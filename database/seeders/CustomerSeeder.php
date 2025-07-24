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
            ['user_id' => 1],

            // ----------- 2
            ['user_id' => 2],

            // ----------- 3
            ['user_id' => 3],

            // ----------- 4
            ['user_id' => 4],

            // ----------- 5
            ['user_id' => 5],

            // ----------- 6
            ['user_id' => 6],

            // ----------- 7
            ['user_id' => 7],

            // ----------- 8
            ['user_id' => 8],

            // ----------- 9
            ['user_id' => 9],

            // ----------- 10
            ['user_id' => 10],

            // ----------- 11
            ['user_id' => 11],

            // ----------- 12
            ['user_id' => 12],

            // ----------- 13
            ['user_id' => 13],

            // ----------- 14
            ['user_id' => 14],

            // ----------- 15
            ['user_id' => 15],

            // ----------- 16
            ['user_id' => 16],

            // ----------- 17
            ['user_id' => 17],

            // ----------- 18
            ['user_id' => 18],

            // ----------- 19
            ['user_id' => 19],

            // ----------- 20
            ['user_id' => 20],

            // ----------- 21
            ['user_id' => 21],

            // ----------- 22
            ['user_id' => 22],

            // ----------- 23
            ['user_id' => 23],

            // ----------- 24
            ['user_id' => 24],

            // ----------- 25
            ['user_id' => 25],

            // ----------- 26
            ['user_id' => 26],

            // ----------- 27
            ['user_id' => 27],
        ];

        foreach ($customers as $c) {
            Customer::create($c);
        }
    }
}
