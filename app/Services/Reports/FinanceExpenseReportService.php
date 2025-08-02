<?php

namespace App\Services\Reports;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class FinanceExpenseReportService
{
    public static function generate(Carbon $startDate, Carbon $endDate): Collection
    {
        $expenses = Expense::whereBetween('date', [$startDate, $endDate])->get();

        $total = $expenses->sum('price');
        $days = (int) $startDate->diffInDays($endDate) + 1;

        $averagePerDay = $days > 0 ? $total / $days : 0;
        $averagePerTransaction = $expenses->count() > 0 ? $total / $expenses->count() : 0;

        $groupedByDay = $expenses->groupBy(
            fn($expense) => Carbon::parse($expense->date)->format('Y-m-d')
        )->map(fn($group) => $group->sum('price'));

        $topDay = $groupedByDay->sortDesc()->keys()->first();
        $topDayAmount = $groupedByDay->max() ?? 0;

        $bottomDay = $groupedByDay->sort()->keys()->first();
        $bottomDayAmount = $groupedByDay->min() ?? 0;

        // Process expenses by needs - convert to array collection like Income report
        $expensesByNeeds = $expenses
            ->groupBy('needs')
            ->map(function ($grouped, $needs) {
                return [
                    'needs' => $needs ?? 'Tidak ada kebutuhan',
                    'jumlah_transaksi' => $grouped->count(),
                    'total_pengeluaran' => $grouped->sum('price'),
                ];
            })
            ->sortByDesc('total_pengeluaran')
            ->values();

        return collect([
            'total' => $total,
            'totalDays' => $days,
            'totalTransactions' => $expenses->count(),
            'averagePerDay' => $averagePerDay,
            'averagePerTransaction' => $averagePerTransaction,
            'topDay' => $topDay,
            'topDayAmount' => $topDayAmount,
            'bottomDay' => $bottomDay,
            'bottomDayAmount' => $bottomDayAmount,
            'expensesByNeeds' => $expensesByNeeds,
        ]);
    }

    public static function generatePdf(string $name, Carbon $startDate, Carbon $endDate): string
    {
        $data = self::generate($startDate, $endDate);

        // Extract data for PDF
        $total = $data['total'];
        $totalDays = $data['totalDays'];
        $averagePerDay = $data['averagePerDay'];
        $averagePerTransaction = $data['averagePerTransaction'];
        $topDay = $data['topDay'];
        $topDayAmount = $data['topDayAmount'];
        $bottomDay = $data['bottomDay'];
        $bottomDayAmount = $data['bottomDayAmount'];
        $expensesByNeeds = $data['expensesByNeeds'];

        $html = view('pdf.finance-expense', [
            'name' => $name,
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $totalDays,
            'total' => $total,
            'averagePerDay' => $averagePerDay,
            'averagePerTransaction' => $averagePerTransaction,
            'topDay' => $topDay ? Carbon::parse($topDay)->translatedFormat('j F Y') : '-',
            'topDayAmount' => $topDayAmount,
            'bottomDay' => $bottomDay ? Carbon::parse($bottomDay)->translatedFormat('j F Y') : '-',
            'bottomDayAmount' => $bottomDayAmount,
            'expensesByNeeds' => $expensesByNeeds,
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
