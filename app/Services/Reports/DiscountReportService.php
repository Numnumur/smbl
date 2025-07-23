<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;

class DiscountReportService
{
    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate)
    {
        $days = (int) $startDate->diffInDays($endDate) + 1;
        $totalDays = $days;

        $orders = Order::query()
            ->whereNotNull('discount_name')
            ->where('status', 'Selesai')
            ->whereBetween('exit_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $totalDiscount = $orders->sum(function ($order) {
            return $order->discount_type === 'Persentase'
                ? $order->total_price_before_discount - $order->total_price
                : $order->discount_value;
        });

        $totalUsage = $orders->count();

        $byDiscount = $orders->groupBy('discount_name')->map(function ($group, $name) {
            return [
                'name' => $name ?? '-',
                'count' => $group->count(),
                'total_value' => $group->sum(function ($order) {
                    return $order->discount_type === 'Persentase'
                        ? $order->total_price_before_discount - $order->total_price
                        : $order->discount_value;
                }),
            ];
        })->values();

        $byPackage = $orders->groupBy(fn($order) => $order->discount_name . '_' . $order->order_package)->map(function ($group) {
            $first = $group->first();
            return [
                'discount' => $first->discount_name ?? '-',
                'type' => $first->discount_type ?? '-',
                'value' => $first->discount_value ?? 0,
                'package' => $first->order_package ?? '-',
                'count' => $group->count(),
                'total_value' => $group->sum(function ($order) {
                    return $order->discount_type === 'Persentase'
                        ? $order->total_price_before_discount - $order->total_price
                        : $order->discount_value;
                }),
            ];
        })->values();

        $byType = $orders->groupBy('type')->map(function ($group, $type) {
            return [
                'type' => $type ?? '-',
                'count' => $group->count(),
                'total_value' => $group->sum(function ($order) {
                    return $order->discount_type === 'Persentase'
                        ? $order->total_price_before_discount - $order->total_price
                        : $order->discount_value;
                }),
            ];
        })->values();

        $html = view('pdf.discount-report', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $totalDays,
            'totalUsage' => $totalUsage,
            'totalDiscount' => $totalDiscount,
            'byDiscount' => $byDiscount,
            'byPackage' => $byPackage,
            'byType' => $byType,
        ])->render();

        // Kembalikan file PDF
        return Browsershot::html($html)->pdf();
    }
}
