<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Expense;
use App\Services\Reports\FinanceIncomeReportService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class FinanceIncomeReport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Laporan Pemasukan';
    protected static ?string $title = 'Laporan Keuangan - Pemasukan';
    protected static string $view = 'filament.pages.finance-income-report';
    protected static ?string $navigationGroup = 'Laporan';

    public ?array $data = [];
    public $reportData = null;

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->generateReport()),
                        DatePicker::make('end_date')
                            ->label('Hingga Tanggal')
                            ->native(false)
                            ->required()
                            ->afterOrEqual('start_date')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->generateReport()),
                    ])->columns(2)
            ])
            ->statePath('data');
    }

    public function generateReport(): void
    {
        $data = $this->form->getState();

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        $orders = Order::where('status', 'Selesai')
            ->whereBetween('exit_date', [$startDate, $endDate])
            ->get();

        $total = $orders->sum('total_price');
        $days = (int) $startDate->diffInDays($endDate) + 1;

        $expenses = Expense::whereBetween('date', [$startDate, $endDate])->get();
        $totalExpense = $expenses->sum('price');
        $netProfit = $total - $totalExpense;

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

        $this->reportData = [
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'total' => $total,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'averagePerDay' => $averagePerDay,
            'averagePerOrder' => $averagePerOrder,
            'topDay' => $topDay ? Carbon::parse($topDay)->translatedFormat('j F Y') : '-',
            'topDayAmount' => $topDayAmount,
            'bottomDay' => $bottomDay ? Carbon::parse($bottomDay)->translatedFormat('j F Y') : '-',
            'bottomDayAmount' => $bottomDayAmount,
            'ordersByPackage' => $ordersByPackage,
            'ordersByType' => $ordersByType,
            'totalOrders' => $orders->count(),
        ];
    }

    public function getSummaryData()
    {
        if (!$this->reportData) {
            return null;
        }

        return [
            'period' => $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'] . ' (' . $this->reportData['totalDays'] . ' hari)',
            'total' => 'Rp ' . number_format($this->reportData['total'], 0, ',', '.'),
            'totalExpense' => 'Rp ' . number_format($this->reportData['totalExpense'], 0, ',', '.'),
            'netProfit' => 'Rp ' . number_format($this->reportData['netProfit'], 0, ',', '.'),
            'netProfitRaw' => $this->reportData['netProfit'],
            'totalOrders' => number_format($this->reportData['totalOrders']),
            'averagePerDay' => 'Rp ' . number_format($this->reportData['averagePerDay'], 0, ',', '.'),
            'averagePerOrder' => 'Rp ' . number_format($this->reportData['averagePerOrder'], 0, ',', '.'),
            'topDay' => $this->reportData['topDay'] !== '-'
                ? $this->reportData['topDay'] . ' (Rp. ' . number_format($this->reportData['topDayAmount'], 0, ',', '.') . ')'
                : '-',
            'bottomDay' => $this->reportData['bottomDay'] !== '-'
                ? $this->reportData['bottomDay'] . ' (Rp. ' . number_format($this->reportData['bottomDayAmount'], 0, ',', '.') . ')'
                : '-',
        ];
    }

    public function getPackageData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['ordersByPackage'];
    }

    public function getTypeData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['ordersByType'];
    }

    public function getTitle(): string
    {
        return 'Laporan Keuangan - Pemasukan';
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('cetak_laporan')
                ->label('Cetak Laporan PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->modalHeading('Cetak Laporan PDF')
                ->modalWidth('md')
                ->form([
                    Section::make('Informasi Laporan')
                        ->description('Masukkan nama untuk laporan PDF yang akan dibuat')
                        ->schema([
                            TextInput::make('report_name')
                                ->label('Nama Laporan')
                                ->required()
                                ->placeholder('Contoh: Laporan Pemasukan Januari 2025')
                                ->default(function () {
                                    if ($this->reportData) {
                                        return 'Laporan Pemasukan ' . $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'];
                                    }
                                    return 'Laporan Pemasukan';
                                })
                                ->helperText('Nama ini akan muncul sebagai judul di laporan PDF')
                                ->maxLength(100)
                                ->columnSpanFull(),
                        ]),

                    Section::make('Detail Laporan')
                        ->description('Informasi periode dan data yang akan dicetak')
                        ->schema([
                            TextInput::make('periode_info')
                                ->label('Periode')
                                ->disabled()
                                ->default(function () {
                                    if ($this->reportData) {
                                        return $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'] . ' (' . $this->reportData['totalDays'] . ' hari)';
                                    }
                                    return '-';
                                }),
                            TextInput::make('total_pemasukan_info')
                                ->label('Total Pemasukan')
                                ->disabled()
                                ->default(function () {
                                    if ($this->reportData) {
                                        return 'Rp ' . number_format($this->reportData['total'], 0, ',', '.');
                                    }
                                    return '-';
                                }),
                        ])->columns(2),
                ])
                ->modalSubmitActionLabel('Cetak PDF')
                ->modalCancelActionLabel('Batal')
                ->action(function (array $data) {
                    if (!$this->reportData) {
                        Notification::make()
                            ->title('Error')
                            ->body('Tidak ada data laporan. Silakan pilih rentang tanggal terlebih dahulu.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $reportData = [
                            'name' => $data['report_name'],
                            'start_date' => $this->data['start_date'],
                            'end_date' => $this->data['end_date'],
                        ];

                        $pdf = FinanceIncomeReportService::generate($reportData);

                        Notification::make()
                            ->title('PDF Berhasil Dibuat')
                            ->body('Laporan PDF "' . $data['report_name'] . '" berhasil dibuat dan siap diunduh.')
                            ->success()
                            ->send();

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['report_name'] . '.pdf');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Terjadi kesalahan saat membuat PDF: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->disabled(fn() => !$this->reportData || empty($this->reportData['total']))
                ->visible(fn() => $this->reportData !== null),
        ];
    }
}
