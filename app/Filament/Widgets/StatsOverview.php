<?php

namespace App\Filament\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use Illuminate\Support\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '20s';

    protected function getHeading(): ?string
    {
        return 'Statistik Pesanan Bulan Ini';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $ordersThisMonth = Order::whereBetween('entry_date', [$startOfMonth, $endOfMonth])->get();

        $customersThisMonth = $ordersThisMonth->pluck('customer_id')->unique()->count();

        $popularPackageData = $ordersThisMonth
            ->groupBy('order_package')
            ->map(fn($orders) => $orders->count())
            ->sortDesc()
            ->take(1);

        $popularPackageName = $popularPackageData->keys()->first() ?? '-';
        $popularPackageCount = $popularPackageData->values()->first() ?? 0;

        return [
            Stat::make('pelanggan', $customersThisMonth)
                ->label('Pelanggan')
                ->description('Total Pelanggan')
                ->descriptionIcon('heroicon-o-users', IconPosition::Before)
                ->color('sky'),

            Stat::make('pesanan', $ordersThisMonth->count())
                ->label('Pesanan')
                ->description('Total Pesanan')
                ->descriptionIcon('heroicon-o-clipboard-document-check', IconPosition::Before)
                ->color('violet'),

            Stat::make('populer', $popularPackageName)
                ->label('Paket Pesanan Populer')
                ->description("Telah dipesan sebanyak {$popularPackageCount} kali")
                ->descriptionIcon('heroicon-o-shopping-bag', IconPosition::Before)
                ->color('success'),
        ];
    }
}
