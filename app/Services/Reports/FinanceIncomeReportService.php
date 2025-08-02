<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class FinanceIncomeReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate): Collection
    {
        $orders = Order::where('status', 'Selesai')
            ->whereBetween('exit_date', [$startDate, $endDate])
            ->get();

        $total = $orders->sum('total_price');
        $days = (int) $startDate->diffInDays($endDate) + 1;

        $averagePerDay = $days > 0 ? $total / $days : 0;
        $averagePerOrder = $orders->count() > 0 ? $total / $orders->count() : 0;

        $groupedByDay = $orders->groupBy(
            fn($order) => Carbon::parse($order->exit_date)->format('Y-m-d')
        )->map(fn($group) => $group->sum('total_price'));

        $topDay = $groupedByDay->sortDesc()->keys()->first();
        $topDayAmount = $groupedByDay->max() ?? 0;

        $bottomDay = $groupedByDay->sort()->keys()->first();
        $bottomDayAmount = $groupedByDay->min() ?? 0;

        // Process orders by package - convert to array collection like OrderWork
        $ordersByPackage = $orders
            ->groupBy('order_package')
            ->map(function ($grouped, $package) {
                return [
                    'order_package' => $package ?? 'Tidak ada paket',
                    'jumlah_pesanan' => $grouped->count(),
                    'total_pemasukan' => $grouped->sum('total_price'),
                ];
            })
            ->sortByDesc('total_pemasukan')
            ->values();

        // Process orders by type - convert to array collection like OrderWork  
        $ordersByType = $orders
            ->groupBy('type')
            ->map(function ($grouped, $type) {
                return [
                    'type' => $type ?? 'Tidak ada tipe',
                    'jumlah_pesanan' => $grouped->count(),
                    'total_pemasukan' => $grouped->sum('total_price'),
                ];
            })
            ->sortByDesc('total_pemasukan')
            ->values();

        return collect([
            'total' => $total,
            'totalDays' => $days,
            'totalOrders' => $orders->count(),
            'averagePerDay' => $averagePerDay,
            'averagePerOrder' => $averagePerOrder,
            'topDay' => $topDay,
            'topDayAmount' => $topDayAmount,
            'bottomDay' => $bottomDay,
            'bottomDayAmount' => $bottomDayAmount,
            'ordersByPackage' => $ordersByPackage,
            'ordersByType' => $ordersByType,
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate): string
    {
        $data = self::generate($startDate, $endDate);

        // Extract data for PDF
        $total = $data['total'];
        $totalDays = $data['totalDays'];
        $averagePerDay = $data['averagePerDay'];
        $averagePerOrder = $data['averagePerOrder'];
        $topDay = $data['topDay'];
        $topDayAmount = $data['topDayAmount'];
        $bottomDay = $data['bottomDay'];
        $bottomDayAmount = $data['bottomDayAmount'];
        $ordersByPackage = $data['ordersByPackage'];
        $ordersByType = $data['ordersByType'];

        $html = view('pdf.finance-income', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $totalDays,
            'total' => $total,
            'averagePerDay' => $averagePerDay,
            'averagePerOrder' => $averagePerOrder,
            'topDay' => $topDay ? Carbon::parse($topDay)->translatedFormat('j F Y') : '-',
            'topDayAmount' => $topDayAmount,
            'bottomDay' => $bottomDay ? Carbon::parse($bottomDay)->translatedFormat('j F Y') : '-',
            'bottomDayAmount' => $bottomDayAmount,
            'ordersByPackage' => $ordersByPackage,
            'ordersByType' => $ordersByType,
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
