<?php

namespace App\Filament\Pages;

use App\Services\Reports\CustomerReportService;
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

class CustomerReport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pesanan Pelanggan';
    protected static ?int $navigationSort = 14;
    protected static ?string $title = 'Laporan Pesanan Pelanggan';
    protected static string $view = 'filament.pages.customer-report';
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

        $customers = CustomerReportService::generate($startDate, $endDate);
        $days = (int) $startDate->diffInDays($endDate) + 1;

        // Calculate statistics
        $totalCustomers = $customers->count();
        $totalOrders = $customers->sum('total_orders');
        $totalIncome = $customers->sum('total_income');
        $averageOrdersPerCustomer = $totalCustomers > 0 ? round($totalOrders / $totalCustomers, 2) : 0;
        $averageIncomePerCustomer = $totalCustomers > 0 ? round($totalIncome / $totalCustomers, 2) : 0;

        // Top customer by orders and income
        $topCustomerByOrders = $customers->sortByDesc('total_orders')->first();
        $topCustomerByIncome = $customers->sortByDesc('total_income')->first();

        $this->reportData = [
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => $days,
            'customers' => $customers,
            'totalCustomers' => $totalCustomers,
            'totalOrders' => $totalOrders,
            'totalIncome' => $totalIncome,
            'averageOrdersPerCustomer' => $averageOrdersPerCustomer,
            'averageIncomePerCustomer' => $averageIncomePerCustomer,
            'topCustomerByOrders' => $topCustomerByOrders,
            'topCustomerByIncome' => $topCustomerByIncome,
        ];
    }

    public function getSummaryData()
    {
        if (!$this->reportData) {
            return null;
        }

        return [
            'period' => $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'] . ' (' . $this->reportData['totalDays'] . ' hari)',
            'totalCustomers' => number_format($this->reportData['totalCustomers']),
            'totalOrders' => number_format($this->reportData['totalOrders']),
            'totalIncome' => 'Rp ' . number_format($this->reportData['totalIncome'], 0, ',', '.'),
            'averageOrdersPerCustomer' => number_format($this->reportData['averageOrdersPerCustomer'], 1),
            'averageIncomePerCustomer' => 'Rp ' . number_format($this->reportData['averageIncomePerCustomer'], 0, ',', '.'),
            'topCustomerByOrders' => $this->reportData['topCustomerByOrders']
                ? $this->reportData['topCustomerByOrders']['name'] . ' (' . $this->reportData['topCustomerByOrders']['total_orders'] . ' pesanan)'
                : '-',
            'topCustomerByIncome' => $this->reportData['topCustomerByIncome']
                ? $this->reportData['topCustomerByIncome']['name'] . ' (Rp ' . number_format($this->reportData['topCustomerByIncome']['total_income'], 0, ',', '.') . ')'
                : '-',
        ];
    }

    public function getCustomersData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['customers'];
    }

    public function getTitle(): string
    {
        return 'Laporan Pesanan Pelanggan';
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
                                ->placeholder('Contoh: Laporan Pelanggan Januari 2025')
                                ->default(function () {
                                    if ($this->reportData) {
                                        return 'Laporan Pelanggan ' . $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'];
                                    }
                                    return 'Laporan Pelanggan';
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

                        $pdf = CustomerReportService::generatePdf($data['report_name'], $startDate, $endDate);

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
                ->disabled(fn() => !$this->reportData || $this->reportData['totalCustomers'] == 0)
                ->visible(fn() => $this->reportData !== null),
        ];
    }
}
