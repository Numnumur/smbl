<?php

namespace App\Filament\Pages;

use App\Services\Reports\OrderWorkReportService;
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

class OrderWorkReport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Laporan Pengerjaan';
    protected static ?string $title = 'Laporan Pengerjaan Pesanan';
    protected static string $view = 'filament.pages.order-work-report';
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

        $reportData = OrderWorkReportService::generate($startDate, $endDate);
        $days = (int) $startDate->diffInDays($endDate) + 1;

        // Calculate additional statistics
        $totalPesananMasuk = $reportData['totalPesananMasuk'];
        $totalPesananSelesai = $reportData['totalPesananSelesai'];
        $totalPaketPesanan = $reportData['ordersByPackage']->count();
        $totalTipePaketPesanan = $reportData['ordersByType']->count();

        // Most popular package and type
        $paketTerpopuler = $reportData['ordersByPackage']->first();
        $tipeTerpopuler = $reportData['ordersByType']->first();

        $this->reportData = [
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'totalPesananMasuk' => $totalPesananMasuk,
            'totalPesananSelesai' => $totalPesananSelesai,
            'totalPaketPesanan' => $totalPaketPesanan,
            'totalTipePaketPesanan' => $totalTipePaketPesanan,
            'paketTerpopuler' => $paketTerpopuler,
            'tipeTerpopuler' => $tipeTerpopuler,
            'ordersByPackage' => $reportData['ordersByPackage'],
            'ordersByType' => $reportData['ordersByType'],
            'ordersKarpetByUkuran' => $reportData['ordersKarpetByUkuran'],
        ];
    }

    public function getSummaryData()
    {
        if (!$this->reportData) {
            return null;
        }

        return [
            'period' => $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'] . ' (' . $this->reportData['totalDays'] . ' hari)',
            'totalPesananMasuk' => number_format($this->reportData['totalPesananMasuk']),
            'totalPesananSelesai' => number_format($this->reportData['totalPesananSelesai']),
            'totalPaketPesanan' => number_format($this->reportData['totalPaketPesanan']),
            'totalTipePaketPesanan' => number_format($this->reportData['totalTipePaketPesanan']),
            'paketTerpopuler' => $this->reportData['paketTerpopuler']
                ? $this->reportData['paketTerpopuler']['package'] . ' (' . $this->reportData['paketTerpopuler']['jumlah_pesanan'] . ' pesanan)'
                : '-',
            'tipeTerpopuler' => $this->reportData['tipeTerpopuler']
                ? $this->reportData['tipeTerpopuler']['type'] . ' (' . $this->reportData['tipeTerpopuler']['jumlah_pesanan'] . ' pesanan)'
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

    public function getKarpetData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['ordersKarpetByUkuran'];
    }

    public function getTitle(): string
    {
        return 'Laporan Pengerjaan Pesanan';
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
                                ->placeholder('Contoh: Laporan Pengerjaan Januari 2025')
                                ->default(function () {
                                    if ($this->reportData) {
                                        return 'Laporan Pengerjaan ' . $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'];
                                    }
                                    return 'Laporan Pengerjaan';
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

                        $pdf = OrderWorkReportService::generatePdf($data['report_name'], $startDate, $endDate);

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
                ->disabled(fn() => !$this->reportData || $this->reportData['totalPesananMasuk'] == 0)
                ->visible(fn() => $this->reportData !== null),
        ];
    }
}
