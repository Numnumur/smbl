<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Illuminate\Support\Carbon;
use App\Models\Order;
use App\Models\PickupDelivery;
use Filament\Support\Enums\IconPosition;

class CustomerStatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '20s';

    protected function getHeading(): ?string
    {
        return 'Informasi Pelanggan';
    }

    protected function getStats(): array
    {
        $customer = auth()->user()?->customer;

        if (!$customer) {
            return [];
        }

        $orders = $customer->orders();
        $pickupDeliveries = $customer->pickupDeliveries();

        $lastOrder = $orders->latest('entry_date')->first();
        $lastOrderPackage = $lastOrder?->order_package ?? '-';
        $lastOrderDaysAgo = $lastOrder
            ? Carbon::parse($lastOrder->entry_date)->diffForHumans(null, true) . ' lalu'
            : 'Tidak ada data';

        $thisMonthOrderCount = $orders
            ->whereMonth('entry_date', now()->month)
            ->whereYear('entry_date', now()->year)
            ->count();

        $lastPickup = $pickupDeliveries
            ->where('status', '!=', 'Ditolak')
            ->latest('created_at')
            ->first();

        $lastPickupType = $lastPickup ? 'Permintaan ' . ucfirst($lastPickup->type) : 'Tidak ada data';

        $lastPickupDaysAgo = $lastPickup
            ? Carbon::parse($lastPickup->created_at)->diffForHumans(null, true) . ' lalu'
            : 'Tidak ada data';

        $thisMonthPickupCount = $pickupDeliveries
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

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

            Stat::make('antar_jemput_terakhir', $lastPickupType)
                ->label('Permintaan Antar Jemput Terakhir')
                ->description($lastPickupDaysAgo)
                ->descriptionIcon('heroicon-o-archive-box', IconPosition::Before)
                ->color('success'),

            Stat::make('antar_jemput_bulan_ini', $thisMonthPickupCount)
                ->label('Antar Jemput Bulan Ini')
                ->description('Total Permintaan')
                ->descriptionIcon('heroicon-o-archive-box', IconPosition::Before)
                ->color('info'),
        ];
    }
}
