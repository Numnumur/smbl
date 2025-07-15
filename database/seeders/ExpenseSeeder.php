<?php

namespace Database\Seeders;

use App\Models\Expense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenses = [
            [
                'needs' => 'Parfum',
                'detail' => null,
                'date' => '2025-6-2',
                'price' => 98000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-6-4',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-6-4',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-6-8',
                'price' => 121200,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-6-10',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-6-12',
                'price' => 52000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-6-15',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-6-17',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-6-18',
                'price' => 129100,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-6-20',
                'price' => 10000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-6-26',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-6-26',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-6-28',
                'price' => 122200,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-6-30',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-7-2',
                'price' => 52000,
            ],
            [
                'needs' => 'Parfum',
                'detail' => null,
                'date' => '2025-7-6',
                'price' => 86000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-6',
                'price' => 10000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-7-9',
                'price' => 129100,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-12',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-7-13',
                'price' => 52000,
            ],
        ];

        foreach ($expenses as $e) {
            Expense::create($e);
        }
    }
}
