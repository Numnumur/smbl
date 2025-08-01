<?php

namespace App\Services\Reports;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Browsershot\Browsershot;

class CustomerReportService
{
    /**
     * Menghasilkan data laporan pelanggan berdasarkan rentang tanggal.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public static function generate(Carbon $startDate, Carbon $endDate): Collection
    {
        return Customer::with(['user', 'orders'])->get()->map(function ($customer) use ($startDate, $endDate) {
            // Filter pesanan berdasarkan rentang tanggal
            $orders = $customer->orders->whereBetween('entry_date', [$startDate, $endDate]);

            $totalOrders = $orders->count();
            $totalIncome = $orders->sum('total_price');
            $averageIncome = $totalOrders > 0 ? round($totalIncome / $totalOrders, 2) : 0;

            $lastOrder = $orders->sortByDesc('entry_date')->first();
            $lastOrderDate = $lastOrder?->entry_date ? Carbon::parse($lastOrder->entry_date) : null;
            $lastOrderDiff = $lastOrderDate
                ? self::humanReadableDiff($lastOrderDate, $endDate)
                : '-';

            $packageSummary = $orders->groupBy('order_package')->map(fn($orders) => $orders->count());

            return [
                'name' => $customer->user->name ?? 'Tidak diketahui',
                'total_orders' => $totalOrders,
                'last_order_date' => $lastOrderDate ? $lastOrderDate->translatedFormat('j F Y') : '-',
                'last_order_diff' => $lastOrderDiff,
                'total_income' => $totalIncome,
                'average_income' => $averageIncome,
                'packages' => $packageSummary,
            ];
        })->filter(fn($data) => $data['total_orders'] > 0)->values();
    }


    /**
     * Menghasilkan file PDF laporan pelanggan.
     *
     * @param string $name
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return string
     */
    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate)
    {
        // Mendapatkan data detail pelanggan
        $customers = self::generate($startDate, $endDate);

        // Menghitung statistik ringkasan dari data yang sudah ada
        $totalCustomers = $customers->count();
        $totalOrders = $customers->sum('total_orders');
        $totalIncome = $customers->sum('total_income');
        $averageOrdersPerCustomer = $totalCustomers > 0 ? $totalOrders / $totalCustomers : 0;
        $averageIncomePerCustomer = $totalCustomers > 0 ? $totalIncome / $totalCustomers : 0;
        $topCustomerByOrders = $customers->sortByDesc('total_orders')->first();
        $topCustomerByIncome = $customers->sortByDesc('total_income')->first();

        $days = (int) $startDate->diffInDays($endDate) + 1;

        $html = view('pdf.customer-report', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'customers' => $customers,
            'totalCustomers' => $totalCustomers,
            'totalOrders' => $totalOrders,
            'totalIncome' => $totalIncome,
            'averageOrdersPerCustomer' => $averageOrdersPerCustomer,
            'averageIncomePerCustomer' => $averageIncomePerCustomer,
            'topCustomerByOrders' => $topCustomerByOrders,
            'topCustomerByIncome' => $topCustomerByIncome,
        ])->render();

        return Browsershot::html($html)->pdf();
    }

    /**
     * Mengubah selisih tanggal menjadi format yang mudah dibaca.
     *
     * @param Carbon $date
     * @param Carbon $endDate
     * @return string
     */
    protected static function humanReadableDiff(Carbon $date, Carbon $endDate): string
    {
        $diff = $date->diffForHumans($endDate, ['parts' => 2, 'short' => true]);

        $replacements = [
            'mgg' => ' minggu',
            'bln' => ' bulan',
            'thn' => ' tahun',
            'hr'  => ' hari',
            'j'   => ' jam',
            'mnt' => ' menit',
            'd'   => ' detik',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $diff);
    }
}
