<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class FinancialChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = '20s';

    protected static ?string $heading = 'Grafik Keuangan Bulan Ini';

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    protected function getData(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $daysInMonth = $startOfMonth->daysInMonth;

        $labels = [];
        $incomeData = array_fill(0, $daysInMonth, 0);
        $expenseData = array_fill(0, $daysInMonth, 0);

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        $orders = Order::selectRaw('DAY(exit_date) as day, SUM(total_price) as total')
            ->where('status', 'Selesai')
            ->whereBetween('exit_date', [$startOfMonth, $endOfMonth])
            ->groupBy(DB::raw('DAY(exit_date)'))
            ->pluck('total', 'day');

        foreach ($orders as $day => $total) {
            $incomeData[$day - 1] = (float) $total;
        }

        $expenses = Expense::selectRaw('DAY(date) as day, SUM(price) as total')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->groupBy(DB::raw('DAY(date)'))
            ->pluck('total', 'day');

        foreach ($expenses as $day => $total) {
            $expenseData[$day - 1] = (float) $total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#388E3C',
                    'fill' => true,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'backgroundColor' => '#F44336',
                    'borderColor' => '#D32F2F',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
