<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PickupDelivery;


class PickupDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pickupDeliveries = [
            [
                'date_and_time' => '2025-6-1',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-6-6',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-6-10',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-6-14',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-6-18',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-6-22',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-6-26',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-7-1',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-7-1',
                'type' => 'Antar',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-7-6',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],
            [
                'date_and_time' => '2025-7-11',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 3,
            ],

            [
                'date_and_time' => '2025-7-6',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 5,
            ],
            [
                'date_and_time' => '2025-7-10',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 5,
            ],
            [
                'date_and_time' => '2025-6-12',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 4,
            ],
            [
                'date_and_time' => '2025-6-22',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 4,
            ],
            [
                'date_and_time' => '2025-6-29',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 4,
            ],
            [
                'date_and_time' => '2025-7-3',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 4,
            ],
            [
                'date_and_time' => '2025-7-5',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 4,
            ],
            [
                'date_and_time' => '2025-6-15',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 12,
            ],
            [
                'date_and_time' => '2025-6-20',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 12,
            ],
            [
                'date_and_time' => '2025-6-26',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 12,
            ],
            [
                'date_and_time' => '2025-7-1',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 12,
            ],
            [
                'date_and_time' => '2025-7-5',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 12,
            ],
            [
                'date_and_time' => '2025-7-9',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 12,
            ],
            [
                'date_and_time' => '2025-6-8',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 17,
            ],
            [
                'date_and_time' => '2025-6-16',
                'type' => 'Antar',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 17,
            ],
            [
                'date_and_time' => '2025-6-20',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 17,
            ],
            [
                'date_and_time' => '2025-6-24',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 17,
            ],
            [
                'date_and_time' => '2025-6-29',
                'type' => 'Antar dan Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 17,
            ],
            [
                'date_and_time' => '2025-7-7',
                'type' => 'Jemput',
                'status' => 'Selesai',
                'customer_note' => null,
                'laundry_note' => null,
                'customer_id' => 17,
            ],
        ];

        foreach ($pickupDeliveries as $p) {
            PickupDelivery::create($p);
        }
    }
}
