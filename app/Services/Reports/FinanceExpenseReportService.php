<?php

namespace App\Services\Reports;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class FinanceExpenseReportService
{
    public static function generate(array $data)
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        $expenses = Expense::whereBetween('date', [$startDate, $endDate])->get();

        $total = $expenses->sum('price');
        $days = (int) $startDate->diffInDays($endDate) + 1;
        $totalDays = $days;

        $averagePerDay = $days > 0 ? $total / $days : 0;
        $averagePerTransaction = $expenses->count() > 0 ? $total / $expenses->count() : 0;

        $groupedByDay = $expenses->groupBy(
            fn($expense) => Carbon::parse($expense->date)->format('Y-m-d')
        )->map(fn($group) => $group->sum('price'));

        $topDay = $groupedByDay->sortDesc()->keys()->first();
        $topDayAmount = $groupedByDay->max();

        $expensesByNeeds = Expense::select('needs', DB::raw('COUNT(*) as jumlah_transaksi'), DB::raw('SUM(price) as total_pengeluaran'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('needs')
            ->orderByDesc('total_pengeluaran')
            ->get();

        $html = view('pdf.finance-expense', [
            'name' => $data['name'],
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $totalDays,
            'total' => $total,
            'averagePerDay' => $averagePerDay,
            'averagePerTransaction' => $averagePerTransaction,
            'topDay' => $topDay ? Carbon::parse($topDay)->translatedFormat('j F Y') : '-',
            'topDayAmount' => $topDayAmount,
            'expensesByNeeds' => $expensesByNeeds,
        ])->render();

        return Browsershot::html($html)->pdf();
    }
}
