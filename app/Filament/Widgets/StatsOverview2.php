<?php

namespace App\Filament\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class StatsOverview2 extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '20s';

    protected function getHeading(): ?string
    {
        return 'Statistik Keuangan Bulan Ini';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $ordersThisMonth = Order::whereBetween('entry_date', [$startOfMonth, $endOfMonth])->get();

        $incomesThisMonth = $ordersThisMonth->sum('total_price');

        $expensesThisMonth = Expense::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('price');

        $profitThisMonth = $incomesThisMonth - $expensesThisMonth;

        return [
            Stat::make('pemasukan', 'Rp. ' . number_format($incomesThisMonth, 0, ',', '.'))
                ->label('Pemasukan')
                ->description('Total Pendapatan Dari Pesanan')
                ->descriptionIcon('heroicon-o-arrow-down-circle', IconPosition::Before)
                ->color('success'),

            Stat::make('pengeluaran', 'Rp. ' . number_format($expensesThisMonth, 0, ',', '.'))
                ->label('Pengeluaran')
                ->description('Total Biaya Operasional')
                ->descriptionIcon('heroicon-o-arrow-up-circle', IconPosition::Before)
                ->color('danger'),

            Stat::make('laba', 'Rp. ' . number_format($profitThisMonth, 0, ',', '.'))
                ->label('Laba Bersih')
                ->description('Total Laba Besih')
                ->descriptionIcon('heroicon-o-wallet', IconPosition::Before)
                ->color('info'),
        ];
    }
}
