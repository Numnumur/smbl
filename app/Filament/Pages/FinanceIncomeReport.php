<?php

namespace App\Filament\Pages;

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
use Illuminate\Contracts\View\View;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class FinanceIncomeReport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Keuangan Pemasukan';
    protected static ?int $navigationSort = 11;
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

    public function generateReport(): void
    {
        $data = $this->form->getState();

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        $reportData = FinanceIncomeReportService::generate($startDate, $endDate);

        $this->reportData = [
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $reportData['totalDays'],
            'total' => $reportData['total'],
            'totalOrders' => $reportData['totalOrders'],
            'averagePerDay' => $reportData['averagePerDay'],
            'averagePerOrder' => $reportData['averagePerOrder'],
            'topDay' => $reportData['topDay'],
            'topDayAmount' => $reportData['topDayAmount'],
            'bottomDay' => $reportData['bottomDay'],
            'bottomDayAmount' => $reportData['bottomDayAmount'],
            'ordersByPackage' => $reportData['ordersByPackage'],
            'ordersByType' => $reportData['ordersByType'],
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
            'totalOrders' => number_format($this->reportData['totalOrders']),
            'averagePerDay' => 'Rp ' . number_format($this->reportData['averagePerDay'], 0, ',', '.'),
            'averagePerOrder' => 'Rp ' . number_format($this->reportData['averagePerOrder'], 0, ',', '.'),
            'topDay' => $this->reportData['topDay']
                ? Carbon::parse($this->reportData['topDay'])->translatedFormat('j F Y') . ' (Rp ' . number_format($this->reportData['topDayAmount'], 0, ',', '.') . ')'
                : '-',
            'bottomDay' => $this->reportData['bottomDay']
                ? Carbon::parse($this->reportData['bottomDay'])->translatedFormat('j F Y') . ' (Rp ' . number_format($this->reportData['bottomDayAmount'], 0, ',', '.') . ')'
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
                        $startDate = Carbon::parse($this->data['start_date'])->startOfDay();
                        $endDate = Carbon::parse($this->data['end_date'])->endOfDay();

                        $pdf = FinanceIncomeReportService::generatePdf($data['report_name'], $startDate, $endDate);

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
                ->disabled(fn() => !$this->reportData || $this->reportData['total'] == 0)
                ->visible(fn() => $this->reportData !== null),
        ];
    }
}
