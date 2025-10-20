<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\PickupDelivery;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Carbon;

class CustomerStatsOverview2 extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '20s';

    protected function getHeading(): ?string
    {
        return 'Informasi Antar Jemput Pelanggan';
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

        $pickupDeliveries = $customer->pickupDeliveries()
            ->whereIn('status', ['Sudah Dikonfirmasi', 'Selesai']);

        $lastPickup = $pickupDeliveries
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->first();

        $lastPickupType = $lastPickup
            ? 'Permintaan ' . ucfirst($lastPickup->type)
            : 'Tidak ada data';

        $lastPickupDaysAgo = $lastPickup
            ? Carbon::parse("{$lastPickup->date} {$lastPickup->time}")->diffForHumans(null, true) . ' lalu'
            : 'Tidak ada data';

        $thisMonthPickupCount = $pickupDeliveries
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->count();

        $pickupDeliveriesThisMonth = PickupDelivery::whereIn('status', ['Sudah Dikonfirmasi', 'Selesai'])
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $popularPickupData = $pickupDeliveriesThisMonth
            ->groupBy('type')
            ->map(fn($pickups) => $pickups->count())
            ->sortDesc()
            ->take(1);

        $popularPickupType = $popularPickupData->keys()->first()
            ? ucfirst($popularPickupData->keys()->first())
            : '-';
        $popularPickupCount = $popularPickupData->values()->first() ?? 0;

        return [
            Stat::make('antar_jemput_terakhir', $lastPickupType)
                ->label('Permintaan Antar Jemput Terakhir')
                ->description($lastPickupDaysAgo)
                ->descriptionIcon('heroicon-o-archive-box', IconPosition::Before)
                ->color('success'),

            Stat::make('antar_jemput_bulan_ini', $thisMonthPickupCount)
                ->label('Antar Jemput Bulan Ini')
                ->description('Total Permintaan')
                ->descriptionIcon('heroicon-o-archive-box', IconPosition::Before)
                ->color('danger'),

            Stat::make('antar_jemput_populer', $popularPickupType)
                ->label('Antar Jemput Paling Populer')
                ->description("Jumlah diajukan: {$popularPickupCount} permintaan")
                ->descriptionIcon('heroicon-o-fire', IconPosition::Before)
                ->color('info'),
        ];
    }
}
