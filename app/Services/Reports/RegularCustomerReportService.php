<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class RegularCustomerReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate, int $minOrders): Collection
    {
        $orders = Order::with('customer.user')
            ->where('status', 'Selesai')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        $days = (int) $startDate->diffInDays($endDate) + 1;
        $totalCustomers = $orders->pluck('customer_id')->unique()->count();

        $grouped = $orders->groupBy('customer_id')->filter(function ($orders) use ($minOrders) {
            return $orders->count() >= $minOrders;
        });

        $qualifiedCustomers = $grouped->count();

        // Process customers - convert to array collection like Finance Income
        $customers = $grouped->map(function ($orders) {
            $first = $orders->first();
            $user = $first->customer?->user;
            $customer = $first->customer;

            return [
                'name' => $user?->name ?? '-',
                'whatsapp' => $customer->whatsapp ? '+' . $customer->whatsapp : '-',
                'address' => $customer->address ?? '-',
                'total_orders' => $orders->count(),
            ];
        })->sortBy('name')->values();

        return collect([
            'totalDays' => $days,
            'totalCustomers' => $totalCustomers,
            'qualifiedCustomers' => $qualifiedCustomers,
            'minOrders' => $minOrders,
            'customers' => $customers,
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate, int $minOrders): string
    {
        $data = self::generate($startDate, $endDate, $minOrders);

        // Extract data for PDF
        $totalDays = $data['totalDays'];
        $totalCustomers = $data['totalCustomers'];
        $qualifiedCustomers = $data['qualifiedCustomers'];
        $customers = $data['customers'];

        $html = view('pdf.regular-customer', [
            'name' => $name,
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
