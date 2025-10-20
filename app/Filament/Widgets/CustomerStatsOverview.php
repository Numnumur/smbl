<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Illuminate\Support\Carbon;
use App\Models\Order;
use Filament\Support\Enums\IconPosition;

class CustomerStatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '20s';

    protected function getHeading(): ?string
    {
        return 'Informasi Pesanan Pelanggan';
    }

    protected function getStats(): array
    {
        $customer = auth()->user()?->customer;

        if (!$customer) {
            return [];
        }

        $orders = $customer->orders();

        $lastOrder = $orders->latest('entry_date')->first();
        $lastOrderPackage = $lastOrder?->order_package ?? '-';
        $lastOrderDaysAgo = $lastOrder
            ? Carbon::parse($lastOrder->entry_date)->diffForHumans(null, true) . ' lalu'
            : 'Tidak ada data';

        $thisMonthOrderCount = $orders
            ->whereMonth('entry_date', now()->month)
            ->whereYear('entry_date', now()->year)
            ->count();

        $now = Carbon::now();
        $popularPackageData = Order::whereMonth('entry_date', $now->month)
            ->whereYear('entry_date', $now->year)
            ->get()
            ->groupBy('order_package')
            ->map(fn($orders) => $orders->count())
            ->sortDesc()
            ->take(1);

        $popularPackageName = $popularPackageData->keys()->first() ?? '-';
        $popularPackageCount = $popularPackageData->values()->first() ?? 0;

        return [
            Stat::make('pesanan_terakhir', $lastOrderPackage)
                ->label('Pesanan Terakhir')
                ->description($lastOrderDaysAgo)
                ->descriptionIcon('heroicon-o-shopping-bag', IconPosition::Before)
                ->color('sky'),

            Stat::make('pesanan_bulan_ini', $thisMonthOrderCount)
                ->label('Pesanan Bulan Ini')
                ->description('Total Pesanan')
                ->descriptionIcon('heroicon-o-shopping-bag', IconPosition::Before)
                ->color('violet'),

            Stat::make('pesanan_populer', $popularPackageName)
                ->label('Pesanan Paling Populer')
                ->description("Jumlah dipesan: {$popularPackageCount} pesanan")
                ->descriptionIcon('heroicon-o-fire', IconPosition::Before)
                ->color('success'),
        ];
    }
}
