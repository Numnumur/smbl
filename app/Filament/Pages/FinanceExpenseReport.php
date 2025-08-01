<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Services\Reports\FinanceExpenseReportService;
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

class FinanceExpenseReport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Laporan Pengeluaran';
    protected static ?string $title = 'Laporan Keuangan - Pengeluaran';
    protected static string $view = 'filament.pages.finance-expense-report';
    protected static ?string $navigationGroup = 'Laporan';

    public ?array $data = [];
    public $reportData = null;

    /**
     * Mount the component and initialize the form with default date values.
     * Then, generate the initial report.
     */
    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        $this->generateReport();
    }

    /**
     * Define the form schema for filtering the report data.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rentang Waktu Data')
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
                            ->minDate(fn(callable $get) => $get('start_date'))
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->generateReport())
                            ->helperText('Tidak bisa lebih kecil dari tanggal awal'),
                    ])->columns(2)
            ])
            ->statePath('data');
    }

    /**
     * Generate the report data based on the selected date range.
     */
    public function generateReport(): void
    {
        $data = $this->form->getState();

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

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

        $expensesByNeeds = Expense::select('needs', DB::raw('COUNT(*) as jumlah_transaksi'), DB::raw('SUM(price) as total_pengeluaran'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('needs')
            ->orderByDesc('total_pengeluaran')
            ->get();

        $this->reportData = [
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'total' => $total,
            'totalTransactions' => $expenses->count(),
            'averagePerDay' => $averagePerDay,
            'averagePerTransaction' => $averagePerTransaction,
            'topDay' => $topDay ? Carbon::parse($topDay)->translatedFormat('j F Y') : '-',
            'topDayAmount' => $topDayAmount,
            'expensesByNeeds' => $expensesByNeeds,
        ];
    }

    /**
     * Prepare summary data for the Blade view.
     */
    public function getSummaryData()
    {
        if (!$this->reportData) {
            return null;
        }

        return [
            'period' => $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'] . ' (' . $this->reportData['totalDays'] . ' hari)',
            'total' => 'Rp ' . number_format($this->reportData['total'], 0, ',', '.'),
            'totalTransactions' => number_format($this->reportData['totalTransactions']),
            'averagePerDay' => 'Rp ' . number_format($this->reportData['averagePerDay'], 0, ',', '.'),
            'averagePerTransaction' => 'Rp ' . number_format($this->reportData['averagePerTransaction'], 0, ',', '.'),
            'topDay' => $this->reportData['topDay'] !== '-'
                ? $this->reportData['topDay'] . ' (Rp ' . number_format($this->reportData['topDayAmount'], 0, ',', '.') . ')'
                : '-',
        ];
    }

    /**
     * Get the expense data grouped by needs for the Blade view.
     */
    public function getExpensesByNeedsData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['expensesByNeeds'];
    }

    public function getTitle(): string
    {
        return static::$title;
    }

    /**
     * Define the header actions, including the "Cetak Laporan PDF" button.
     */
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
                                ->placeholder('Contoh: Laporan Pengeluaran Juli 2025')
                                ->default(function () {
                                    if ($this->reportData) {
                                        return 'Laporan Pengeluaran ' . $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'];
                                    }
                                    return 'Laporan Pengeluaran';
                                })
                                ->helperText('Nama ini akan muncul sebagai judul di laporan PDF')
                                ->maxLength(100)
                                ->columnSpanFull(),
                        ]),
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

                        $pdf = FinanceExpenseReportService::generate($reportData);

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
