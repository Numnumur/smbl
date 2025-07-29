<?php

namespace App\Services\Reports;

use App\Models\PickupDelivery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Browsershot\Browsershot;

class PickupDeliveryReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate): Collection
    {
        $pickups = PickupDelivery::with('customer.user')
            ->whereBetween('date_and_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $groupedByCustomer = $pickups->groupBy(fn($p) => $p->customer->user->name ?? 'Tidak diketahui')->map(function ($items) {
            return [
                'total' => $items->count(),
                'detail' => $items->groupBy('type')->map->count(),
            ];
        });

        return collect([
            'total_requests' => $pickups->count(),
            'total_customers' => $pickups->pluck('customer_id')->unique()->count(),
            'requests_by_type' => $pickups->groupBy('type')->map->count(),
            'requests_by_customer' => $groupedByCustomer->sortByDesc('total'),
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate)
    {
        $days = $startDate->diffInDays($endDate) + 1;
        $data = self::generate($startDate, $endDate);

        $html = view('pdf.pickup-delivery-report', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'totalRequests' => $data['total_requests'],
            'totalCustomers' => $data['total_customers'],
            'requestsByType' => $data['requests_by_type'],
            'requestsByCustomer' => $data['requests_by_customer'],
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
