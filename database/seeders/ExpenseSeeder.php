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
                'date' => '2025-9-2',
                'price' => 98000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-9-4',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-9-4',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-9-8',
                'price' => 121200,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-9-10',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-9-12',
                'price' => 52000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-9-15',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-9-17',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-9-18',
                'price' => 129100,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-9-20',
                'price' => 10000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-9-26',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-9-26',
                'price' => 52000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-9-28',
                'price' => 122200,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-9-30',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-10-2',
                'price' => 52000,
            ],
            [
                'needs' => 'Parfum',
                'detail' => null,
                'date' => '2025-10-6',
                'price' => 86000,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-10-6',
                'price' => 10000,
            ],
            [
                'needs' => 'Sabun',
                'detail' => 'Ekonomi, Karbol, Daia',
                'date' => '2025-10-9',
                'price' => 129100,
            ],
            [
                'needs' => 'Plastik',
                'detail' => null,
                'date' => '2025-10-12',
                'price' => 10000,
            ],
            [
                'needs' => 'Listrik',
                'detail' => null,
                'date' => '2025-10-13',
                'price' => 52000,
            ],
        ];

        foreach ($expenses as $e) {
            Expense::create($e);
        }
    }
}
