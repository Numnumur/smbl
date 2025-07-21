<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Browsershot\Browsershot;

class OrderWorkReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate): Collection
    {
        $orders = Order::whereBetween('entry_date', [$startDate, $endDate])->get();
        $ordersFinished = $orders->filter(fn($order) => $order->status !== 'Baru');

        $ordersByPackage = $ordersFinished
            ->groupBy(fn($order) => $order->order_package . '|' . $order->type)
            ->map(function ($grouped) {
                $first = $grouped->first();
                [$package, $type] = explode('|', $first->order_package . '|' . $first->type);

                $jumlah_pesanan = $grouped->count();

                if ($type === 'Karpet') {
                    $detailUkuran = $grouped
                        ->groupBy(fn($order) => (int) $order->length . ' cm x ' . (int) $order->width . ' cm')
                        ->map(fn($g, $ukuran) => $ukuran . ' (' . $g->count() . ')')
                        ->values()
                        ->implode(', ');

                    return [
                        'package' => $package,
                        'type' => $type,
                        'jumlah_pesanan' => $jumlah_pesanan,
                        'total_pengerjaan' => $detailUkuran,
                        'unit' => '',
                    ];
                }

                return [
                    'package' => $package,
                    'type' => $type,
                    'jumlah_pesanan' => $jumlah_pesanan,
                    'total_pengerjaan' => $grouped->sum(fn($order) => self::getTotalPengerjaan($order)),
                    'unit' => self::getUnit($type),
                ];
            })
            ->sortByDesc('jumlah_pesanan')
            ->values();


        $ordersByType = $ordersFinished
            ->groupBy('type')
            ->map(function ($grouped, $type) {
                $jumlah_pesanan = $grouped->count();

                if ($type === 'Karpet') {
                    $detailUkuran = $grouped
                        ->groupBy(fn($order) => (int) $order->length . ' cm x ' . (int) $order->width . ' cm')
                        ->map(fn($g, $ukuran) => $ukuran . ' (' . $g->count() . ')')
                        ->values()
                        ->implode(', ');

                    return [
                        'type' => $type,
                        'jumlah_pesanan' => $jumlah_pesanan,
                        'total_pengerjaan' => $detailUkuran,
                        'unit' => '',
                    ];
                }

                return [
                    'type' => $type,
                    'jumlah_pesanan' => $jumlah_pesanan,
                    'total_pengerjaan' => $grouped->sum(fn($order) => self::getTotalPengerjaan($order)),
                    'unit' => self::getUnit($type),
                ];
            })
            ->sortByDesc('jumlah_pesanan')
            ->values();


        $ordersKarpetByUkuran = $ordersFinished
            ->where('type', 'Karpet')
            ->groupBy(fn($order) => (int) $order->length . ' cm x ' . (int) $order->width . ' cm')
            ->map(function ($grouped, $ukuran) {
                return [
                    'ukuran' => $ukuran,
                    'jumlah' => $grouped->count(),
                ];
            })
            ->sortByDesc('jumlah')
            ->values();


        return collect([
            'totalPesananMasuk' => $orders->count(),
            'totalPesananSelesai' => $ordersFinished->count(),
            'ordersByPackage' => $ordersByPackage,
            'ordersByType' => $ordersByType,
            'ordersKarpetByUkuran' => $ordersKarpetByUkuran,
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate): string
    {
        $data = self::generate($startDate, $endDate);

        $html = view('pdf.order-work-report', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalPesananMasuk' => $data['totalPesananMasuk'],
            'totalPesananSelesai' => $data['totalPesananSelesai'],
            'ordersByPackage' => $data['ordersByPackage'],
            'ordersByType' => $data['ordersByType'],
            'ordersKarpetByUkuran' => $data['ordersKarpetByUkuran'],
        ])->render();

        return Browsershot::html($html)->pdf();
    }

    protected static function getUnit(string $type): string
    {
        return match ($type) {
            'Kiloan' => 'kg',
            'Lembaran' => 'lembar',
            'Satuan' => 'item',
            default => '',
        };
    }


    protected static function getTotalPengerjaan(Order $order): float|int
    {
        return match ($order->type) {
            'Kiloan' => $order->weight ?? 0,
            'Karpet' => ($order->length ?? 0) * ($order->width ?? 0),
            'Lembaran', 'Satuan' => $order->quantity ?? 0,
            default => 0,
        };
    }
}
