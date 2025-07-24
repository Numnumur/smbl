<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // ----------- 1
            ['name' => 'Fadil', 'email' => 'fadil@gmail.com', 'password' => 'password'],

            // ----------- 2
            ['name' => 'Jamal', 'email' => 'jamal@gmail.com', 'password' => 'password'],

            // ----------- 3
            ['name' => 'Daniel', 'email' => 'daniel@gmail.com', 'password' => 'password'],

            // ----------- 4
            ['name' => 'Bu Ema', 'email' => 'buema@gmail.com', 'password' => 'password'],

            // ----------- 5
            ['name' => 'Ambar', 'email' => 'ambar@gmail.com', 'password' => 'password'],

            // ----------- 6
            ['name' => 'Sella', 'email' => 'sella@gmail.com', 'password' => 'password'],

            // ----------- 7
            ['name' => 'Asri', 'email' => 'asri@gmail.com', 'password' => 'password'],

            // ----------- 8
            ['name' => 'Samuel', 'email' => 'samuel@gmail.com', 'password' => 'password'],

            // ----------- 9
            ['name' => 'Putri', 'email' => 'putri@gmail.com', 'password' => 'password'],

            // ----------- 10
            ['name' => 'Ari', 'email' => 'ari@gmail.com', 'password' => 'password'],

            // ----------- 11
            ['name' => 'Hendri', 'email' => 'hendri@gmail.com', 'password' => 'password'],

            // ----------- 12
            ['name' => 'Johan', 'email' => 'johan@gmail.com', 'password' => 'password'],

            // ----------- 13
            ['name' => 'MH', 'email' => 'mh@gmail.com', 'password' => 'password'],

            // ----------- 14
            ['name' => 'Eka', 'email' => 'eka@gmail.com', 'password' => 'password'],

            // ----------- 15
            ['name' => 'Hera', 'email' => 'hera@gmail.com', 'password' => 'password'],

            // ----------- 16
            ['name' => 'Pak Slamet', 'email' => 'pakslamet@gmail.com', 'password' => 'password'],

            // ----------- 17
            ['name' => 'Pak Wilman', 'email' => 'pakwilman@gmail.com', 'password' => 'password'],

            // ----------- 18
            ['name' => 'Fatih', 'email' => 'fatih@gmail.com', 'password' => 'password'],

            // ----------- 19
            ['name' => 'Deswita', 'email' => 'deswita@gmail.com', 'password' => 'password'],

            // ----------- 20
            ['name' => 'Mama Fajar', 'email' => 'mamafajar@gmail.com', 'password' => 'password'],

            // ----------- 21
            ['name' => 'Pak Eko', 'email' => 'pakeko@gmail.com', 'password' => 'password'],

            // ----------- 22
            ['name' => 'Dio', 'email' => 'dio@gmail.com', 'password' => 'password'],

            // ----------- 23
            ['name' => 'Mama Syifa', 'email' => 'mamasyifa@gmail.com', 'password' => 'password'],

            // ----------- 24
            ['name' => 'Mama Tri', 'email' => 'mamatri@gmail.com', 'password' => 'password'],

            // ----------- 25
            ['name' => 'Yan/Dwi', 'email' => 'yandwi@gmail.com', 'password' => 'password'],

            // ----------- 26
            ['name' => 'Eben', 'email' => 'eben@gmail.com', 'password' => 'password'],

            // ----------- 27
            ['name' => 'Bu Jevin', 'email' => 'bujevin@gmail.com', 'password' => 'password'],

            // ----------- 28
            ['name' => 'Admin', 'email' => 'admin@gmail.com', 'password' => 'password'],
        ];

        User::factory()->createMany($users);
    }
}
