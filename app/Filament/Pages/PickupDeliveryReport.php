<?php

namespace App\Filament\Pages;

use App\Services\Reports\PickupDeliveryReportService;
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

class PickupDeliveryReport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Permintaan Antar Jemput';
    protected static ?int $navigationSort = 18;
    protected static ?string $title = 'Laporan Permintaan Antar Jemput';
    protected static string $view = 'filament.pages.pickup-delivery-report';
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

        $reportData = PickupDeliveryReportService::generate($startDate, $endDate);
        $days = (int) $startDate->diffInDays($endDate) + 1;

        // Calculate additional statistics
        $totalRequests = $reportData['total_requests'];
        $totalCustomers = $reportData['total_customers'];
        $totalTypes = $reportData['requests_by_type']->count();

        // Most popular type and customer
        $jenisTerpopuler = $reportData['requests_by_type']->sortByDesc(function ($count) {
            return $count;
        })->first();
        $jenisTerpopulerName = $reportData['requests_by_type']->sortByDesc(function ($count) {
            return $count;
        })->keys()->first();

        $pelangganTerpopuler = $reportData['requests_by_customer']->first();
        $pelangganTerpopulerName = $reportData['requests_by_customer']->keys()->first();

        $this->reportData = [
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'totalRequests' => $totalRequests,
            'totalCustomers' => $totalCustomers,
            'totalTypes' => $totalTypes,
            'jenisTerpopuler' => $jenisTerpopuler ? [
                'type' => $jenisTerpopulerName,
                'count' => $jenisTerpopuler
            ] : null,
            'pelangganTerpopuler' => $pelangganTerpopuler ? [
                'name' => $pelangganTerpopulerName,
                'count' => $pelangganTerpopuler['total']
            ] : null,
            'requestsByType' => $reportData['requests_by_type'],
            'requestsByCustomer' => $reportData['requests_by_customer'],
        ];
    }

    public function getSummaryData()
    {
        if (!$this->reportData) {
            return null;
        }

        return [
            'period' => $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'] . ' (' . $this->reportData['totalDays'] . ' hari)',
            'totalRequests' => number_format($this->reportData['totalRequests']),
            'totalCustomers' => number_format($this->reportData['totalCustomers']),
            'totalTypes' => number_format($this->reportData['totalTypes']),
            'jenisTerpopuler' => $this->reportData['jenisTerpopuler']
                ? $this->reportData['jenisTerpopuler']['type'] . ' (' . $this->reportData['jenisTerpopuler']['count'] . ' permintaan)'
                : '-',
            'pelangganTerpopuler' => $this->reportData['pelangganTerpopuler']
                ? $this->reportData['pelangganTerpopuler']['name'] . ' (' . $this->reportData['pelangganTerpopuler']['count'] . ' permintaan)'
                : '-',
        ];
    }

    public function getTypeData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['requestsByType']->map(function ($count, $type) {
            return [
                'type' => $type,
                'count' => $count,
            ];
        })->sortByDesc('count')->values();
    }

    public function getCustomerData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['requestsByCustomer']->map(function ($data, $name) {
            return [
                'name' => $name,
                'total' => $data['total'],
                'detail' => $data['detail'],
            ];
        })->values();
    }

    public function getTitle(): string
    {
        return 'Laporan Permintaan Antar Jemput';
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
                                ->placeholder('Contoh: Laporan Antar Jemput Januari 2025')
                                ->default(function () {
                                    if ($this->reportData) {
                                        return 'Laporan Antar Jemput ' . $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'];
                                    }
                                    return 'Laporan Antar Jemput';
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

                        $pdf = PickupDeliveryReportService::generatePdf($data['report_name'], $startDate, $endDate);

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
                ->disabled(fn() => !$this->reportData || $this->reportData['totalRequests'] == 0)
                ->visible(fn() => $this->reportData !== null),
        ];
    }
}
