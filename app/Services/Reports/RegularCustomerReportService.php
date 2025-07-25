<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class RegularCustomerReportService
{
    public static function generate(array $data)
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();
        $days = (int) $startDate->diffInDays($endDate) + 1;
        $totalDays = $days;

        $minOrders = $data['kriteria_minimum_pesanan'];

        $orders = Order::with('customer.user')
            ->where('status', 'Selesai')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        $totalCustomers = $orders->pluck('customer_id')->unique()->count();

        $grouped = $orders->groupBy('customer_id')->filter(function ($orders) use ($minOrders) {
            return $orders->count() >= $minOrders;
        });

        $qualifiedCustomers = $grouped->count();

        $customers = $grouped->map(function ($orders) {
            $first = $orders->first();
            $user = $first->customer?->user;

            return [
                'name' => $user?->name ?? '-',
                'orders' => $orders->count(),
            ];
        })->values();

        $html = view('pdf.regular-customer', [
            'name' => $data['name'],
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $totalDays,
            'totalCustomers' => $totalCustomers,
            'qualifiedCustomers' => $qualifiedCustomers,
            'minOrders' => $minOrders,
            'customers' => $customers,
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
