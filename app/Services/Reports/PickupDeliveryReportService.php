<?php

namespace App\Services\Reports;

use App\Models\PickupDelivery;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Browsershot\Browsershot;

class PickupDeliveryReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate): Collection
    {
        $pickups = PickupDelivery::with('customer.user')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        // Group by customer with detailed breakdown
        $requestsByCustomer = $pickups
            ->groupBy(fn($p) => $p->customer->user->name ?? 'Tidak diketahui')
            ->map(function ($items) {
                return [
                    'total' => $items->count(),
                    'detail' => $items->groupBy('type')->map->count(),
                ];
            })
            ->sortByDesc('total');

        // Group by type
        $requestsByType = $pickups
            ->groupBy('type')
            ->map->count()
            ->sortByDesc(function ($count) {
                return $count;
            });

        return collect([
            'total_requests' => $pickups->count(),
            'total_customers' => $pickups->pluck('customer_id')->unique()->count(),
            'requests_by_type' => $requestsByType,
            'requests_by_customer' => $requestsByCustomer,
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate): string
    {
        $days = (int) $startDate->diffInDays($endDate) + 1;
        $data = self::generate($startDate, $endDate);

        // Calculate additional statistics for PDF (same as in page)
        $totalRequests = $data['total_requests'];
        $totalCustomers = $data['total_customers'];
        $totalTypes = $data['requests_by_type']->count();

        // Most popular type and customer
        $jenisTerpopuler = $data['requests_by_type']->sortByDesc(function ($count) {
            return $count;
        })->first();
        $jenisTerpopulerName = $data['requests_by_type']->sortByDesc(function ($count) {
            return $count;
        })->keys()->first();

        $pelangganTerpopuler = $data['requests_by_customer']->first();
        $pelangganTerpopulerName = $data['requests_by_customer']->keys()->first();

        // Get all requests for PDF
        $allRequests = PickupDelivery::with('customer.user')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        $html = view('pdf.pickup-delivery-report', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'totalRequests' => $totalRequests,
            'totalCustomers' => $totalCustomers,
            'totalTypes' => $totalTypes,
            'jenisTerpopuler' => $jenisTerpopuler ? [
                'type' => $jenisTerpopulerName,
                'count' => $jenisTerpopuler
            ] : null,
            'pelangganTerpopuler' => $pelangganTerpopuler ? [
                'name' => $pelangganTerpopulerName,
                'count' => $pelangganTerpopuler['total']
            ] : null,
            'requestsByType' => $data['requests_by_type'],
            'requestsByCustomer' => $data['requests_by_customer'],
            'allRequests' => $allRequests,
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
