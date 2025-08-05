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
                'date' => '2025-7-2',
                'price' => 98000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-4',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-7-4',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-7-8',
                'price' => 121200,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-10',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-7-12',
                'price' => 52000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-15',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-7-17',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-7-18',
                'price' => 129100,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-20',
                'price' => 10000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-26',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-7-26',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-7-28',
                'price' => 122200,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-7-30',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-8-2',
                'price' => 52000,
            ],
            [
                'needs' => 'Parfum',
                'detail' => null,
                'date' => '2025-8-6',
                'price' => 86000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-8-6',
                'price' => 10000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-8-9',
                'price' => 129100,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-8-12',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-8-13',
                'price' => 52000,
            ],
        ];

        foreach ($expenses as $e) {
            Expense::create($e);
        }
    }
}
