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

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $orders = $customer->orders()->where('status', 'Selesai');

        $lastOrder = $orders->latest('exit_date')->first();
        $lastOrderPackage = $lastOrder?->order_package ?? '-';
        $lastOrderDaysAgo = $lastOrder
            ? Carbon::parse($lastOrder->exit_date)->diffForHumans(null, true) . ' lalu'
            : 'Tidak ada data';

        $thisMonthOrderCount = $orders
            ->whereBetween('exit_date', [$startOfMonth, $endOfMonth])
            ->count();

        $ordersThisMonth = Order::where('status', 'Selesai')
            ->whereBetween('exit_date', [$startOfMonth, $endOfMonth])
            ->get();

        $popularPackageData = $ordersThisMonth
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
                ->label('Paket Pesanan Populer')
                ->description("Jumlah dipesan: {$popularPackageCount} pesanan")
                ->descriptionIcon('heroicon-o-fire', IconPosition::Before)
                ->color('success'),
        ];
    }
}
