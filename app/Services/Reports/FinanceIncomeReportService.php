<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class FinanceIncomeReportService
{
    public static function generate(array $data)
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        $orders = Order::where('status', 'Selesai')
            ->whereBetween('exit_date', [$startDate, $endDate])
            ->get();

        $total = $orders->sum('total_price');
        $days = (int) $startDate->diffInDays($endDate) + 1;
        $totalDays = $days;

        $averagePerDay = $days > 0 ? $total / $days : 0;
        $averagePerOrder = $orders->count() > 0 ? $total / $orders->count() : 0;

        $groupedByDay = $orders->groupBy(
            fn($order) => Carbon::parse($order->exit_date)->format('Y-m-d')
        )->map(fn($group) => $group->sum('total_price'));

        $topDay = $groupedByDay->sortDesc()->keys()->first();
        $topDayAmount = $groupedByDay->max() ?? 0;

        $bottomDay = $groupedByDay->sort()->keys()->first();
        $bottomDayAmount = $groupedByDay->min() ?? 0;

        $ordersByPackage = Order::select(
            'order_package',
            DB::raw('COUNT(*) as jumlah_pesanan'),
            DB::raw('SUM(total_price) as total_pemasukan')
        )
            ->where('status', 'Selesai')
            ->whereBetween('exit_date', [$startDate, $endDate])
            ->groupBy('order_package')
            ->orderByDesc('total_pemasukan')
            ->get();

        $ordersByType = Order::select(
            'type',
            DB::raw('COUNT(*) as jumlah_pesanan'),
            DB::raw('SUM(total_price) as total_pemasukan')
        )
            ->where('status', 'Selesai')
            ->whereBetween('exit_date', [$startDate, $endDate])
            ->groupBy('type')
            ->orderByDesc('total_pemasukan')
            ->get();

        $html = view('pdf.finance-income', [
            'name' => $data['name'],
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
