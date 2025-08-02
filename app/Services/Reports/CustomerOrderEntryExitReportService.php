<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Browsershot\Browsershot;

class CustomerOrderEntryExitReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate): Collection
    {
        $orders = Order::with(['customer.user'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                    ->orWhereBetween('exit_date', [$startDate, $endDate]);
            })
            ->get();

        $customers = $orders->pluck('customer')->unique('id')->filter();

        $customersData = $customers->map(function ($customer) use ($orders, $startDate, $endDate) {
            $entryOrders = $orders->where('customer_id', $customer->id)
                ->whereBetween('entry_date', [$startDate, $endDate]);

            $exitOrders = $orders->where('customer_id', $customer->id)
                ->whereBetween('exit_date', [$startDate, $endDate]);

            return [
                'name' => $customer->user->name ?? '-',
                'entry' => $entryOrders->count(),
                'exit' => $exitOrders->count(),
                'entry_dates' => $entryOrders->pluck('entry_date')->sort()->values(),
                'exit_dates' => $exitOrders->pluck('exit_date')->sort()->values(),
            ];
        })->sortByDesc('entry')->values();

        $totalEntry = $customersData->sum('entry');
        $totalExit = $customersData->sum('exit');

        return collect([
            'totalCustomers' => $customers->count(),
            'totalEntry' => $totalEntry,
            'totalExit' => $totalExit,
            'customers' => $customersData,
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate): string
    {
        $data = self::generate($startDate, $endDate);
        $days = (int) $startDate->diffInDays($endDate) + 1;

        // Calculate additional statistics (same as in the page)
        $totalCustomers = $data['totalCustomers'];
        $totalEntry = $data['totalEntry'];
        $totalExit = $data['totalExit'];
        $totalCustomersWithEntry = $data['customers']->where('entry', '>', 0)->count();
        $totalCustomersWithExit = $data['customers']->where('exit', '>', 0)->count();

        // Most active customers (same as in the page)
        $pelangganTerbanyakMasuk = $data['customers']->sortByDesc('entry')->first();
        $pelangganTerbanyakKeluar = $data['customers']->sortByDesc('exit')->first();

        $html = view('pdf.customer-order-entry-exit', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'totalCustomers' => $totalCustomers,
            'totalEntry' => $totalEntry,
            'totalExit' => $totalExit,
            'totalCustomersWithEntry' => $totalCustomersWithEntry,
            'totalCustomersWithExit' => $totalCustomersWithExit,
            'pelangganTerbanyakMasuk' => $pelangganTerbanyakMasuk,
            'pelangganTerbanyakKeluar' => $pelangganTerbanyakKeluar,
            'customers' => $data['customers'],
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
