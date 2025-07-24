<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class CustomerOrderEntryExitReportService
{
    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate)
    {
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $orders = Order::with(['customer.user'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                    ->orWhereBetween('exit_date', [$startDate, $endDate]);
            })
            ->get();

        $customers = $orders->pluck('customer')->unique('id')->filter();

        $data = $customers->map(function ($customer) use ($orders, $startDate, $endDate) {
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
        });


        $totalEntry = $data->sum('entry');
        $totalExit = $data->sum('exit');

        $html = view('pdf.customer-order-entry-exit', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $totalDays,
            'totalCustomers' => $customers->count(),
            'totalEntry' => $totalEntry,
            'totalExit' => $totalExit,
            'customers' => $data,
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
