<?php

namespace App\Services\Reports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Browsershot\Browsershot;

class OrderWorkReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate): array
    {
        // Query dasar dengan status selesai
        $finishedOrders = Order::whereBetween('entry_date', [$startDate, $endDate])
            ->whereNotIn('status', ['Baru', 'Terkendala'])
            ->get();

        // Total semua pesanan (termasuk yang belum selesai)
        $totalPesananMasuk = Order::whereBetween('entry_date', [$startDate, $endDate])->count();

        // Group by package dan type
        $ordersByPackage = $finishedOrders->groupBy(fn($order) => $order->order_package . '|' . $order->type)
            ->map(function ($group, $key) {
                [$package, $type] = explode('|', $key);

                return [
                    'package' => $package,
                    'type' => $type,
                    'jumlah_pesanan' => $group->count(),
                    'total_pengerjaan' => $type === 'Karpet'
                        ? $group->count()
                        : $group->sum(fn($order) => self::getTotalPengerjaan($order)),
                    'unit' => self::getUnit($type),
                    'detail_ukuran' => $type === 'Karpet'
                        ? $group->groupBy(fn($o) => (int)$o->length . ' cm x ' . (int)$o->width . ' cm')
                        ->map(fn($g, $ukuran) => $ukuran . ' (' . $g->count() . ')')
                        ->implode(', ')
                        : ''
                ];
            })
            ->values()
            ->toArray();

        // Group by type saja
        $ordersByType = $finishedOrders->groupBy('type')
            ->map(function ($group, $type) {
                return [
                    'type' => $type,
                    'jumlah_pesanan' => $group->count(),
                    'total_pengerjaan' => $type === 'Karpet'
                        ? $group->count()
                        : $group->sum(fn($order) => self::getTotalPengerjaan($order)),
                    'unit' => self::getUnit($type),
                    'detail_ukuran' => $type === 'Karpet'
                        ? $group->groupBy(fn($o) => (int)$o->length . ' cm x ' . (int)$o->width . ' cm')
                        ->map(fn($g, $ukuran) => $ukuran . ' (' . $g->count() . ')')
                        ->implode(', ')
                        : ''
                ];
            })
            ->values()
            ->toArray();

        return [
            'totalPesananMasuk' => $totalPesananMasuk,
            'totalPesananSelesai' => $finishedOrders->count(),
            'ordersByPackage' => $ordersByPackage,
            'ordersByType' => $ordersByType,
        ];
    }

    public static function getSummary(Carbon $startDate, Carbon $endDate): Collection
    {
        $data = self::generate($startDate, $endDate);

        $totalPengerjaanKiloan = $data['ordersByType']->where('type', 'Kiloan')->sum('total_pengerjaan');
        $totalPengerjaanLembaran = $data['ordersByType']->where('type', 'Lembaran')->sum('total_pengerjaan');
        $totalPengerjaanSatuan = $data['ordersByType']->where('type', 'Satuan')->sum('total_pengerjaan');

        $totalPengerjaanKarpet = $data['ordersByType']->where('type', 'Karpet')->sum('jumlah_pesanan');

        return collect([
            'totalPesananMasuk' => $data['totalPesananMasuk'],
            'totalPesananSelesai' => $data['totalPesananSelesai'],
            'totalOrdersKarpet' => $data['ordersByType']->where('type', 'Karpet')->sum('jumlah_pesanan'),
            'totalOrdersKiloan' => $data['ordersByType']->where('type', 'Kiloan')->sum('jumlah_pesanan'),
            'totalOrdersLembaran' => $data['ordersByType']->where('type', 'Lembaran')->sum('jumlah_pesanan'),
            'totalOrdersSatuan' => $data['ordersByType']->where('type', 'Satuan')->sum('jumlah_pesanan'),
            'totalPengerjaanKarpet' => $totalPengerjaanKarpet,
            'totalPengerjaanKiloan' => $totalPengerjaanKiloan,
            'totalPengerjaanLembaran' => $totalPengerjaanLembaran,
            'totalPengerjaanSatuan' => $totalPengerjaanSatuan,
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate): string
    {
        $report = self::generate($startDate, $endDate);

        $html = view('pdf.order-work-report', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $startDate->diffInDays($endDate) + 1,
            'totalPesananMasuk' => $report['totalPesananMasuk'],
            'totalPesananSelesai' => $report['totalPesananSelesai'],
            'ordersByPackage' => $report['ordersByPackage'],
            'ordersByType' => $report['ordersByType'],
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
