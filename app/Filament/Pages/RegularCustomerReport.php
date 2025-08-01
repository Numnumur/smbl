<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Services\Reports\RegularCustomerReportService;
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

class RegularCustomerReport extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithHeaderActions;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Laporan Pelanggan Tetap';
    protected static ?string $title = 'Laporan Pelanggan Tetap';
    protected static string $view = 'filament.pages.regular-customer-report';
    protected static ?string $navigationGroup = 'Laporan';

    public ?array $data = [];
    public $reportData = null;

    /**
     * Mount the component and initialize the form with default values.
     * Then, generate the initial report.
     */
    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
            'kriteria_minimum_pesanan' => 3, // Nilai default untuk kriteria
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
                Section::make('Rentang Waktu & Kriteria Data')
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
                        TextInput::make('kriteria_minimum_pesanan')
                            ->label('Kriteria Minimum Pesanan')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->generateReport()),
                    ])->columns(3)
            ])
            ->statePath('data');
    }

    /**
     * Generate the report data based on the selected criteria.
     */
    public function generateReport(): void
    {
        $data = $this->form->getState();

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();
        $minOrders = $data['kriteria_minimum_pesanan'];

        $orders = Order::with('customer.user')
            ->where('status', 'Selesai')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->get();

        $totalCustomers = $orders->pluck('customer_id')->unique()->count();

        $grouped = $orders->groupBy('customer_id')->filter(function ($orders) use ($minOrders) {
            return $orders->count() >= $minOrders;
        });

        $qualifiedCustomers = $grouped->count();

        $customers = $grouped->map(function ($orders) {
            $first = $orders->first();
            $user = $first->customer?->user;
            $customer = $first->customer;

            return [
                'name' => $user?->name ?? '-',
                'whatsapp' => $customer->whatsapp ? '+' . $customer->whatsapp : '-',
                'address' => $customer->address ?? '-',
            ];
        })->values();

        $this->reportData = [
            'startDate' => $startDate->translatedFormat('j F Y'),
            'endDate' => $endDate->translatedFormat('j F Y'),
            'totalDays' => (int) $startDate->diffInDays($endDate) + 1,
            'totalCustomers' => $totalCustomers,
            'qualifiedCustomers' => $qualifiedCustomers,
            'minOrders' => $minOrders,
            'customers' => $customers,
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
            'totalCustomers' => number_format($this->reportData['totalCustomers']),
            'minOrders' => number_format($this->reportData['minOrders']),
            'qualifiedCustomers' => number_format($this->reportData['qualifiedCustomers']),
        ];
    }

    /**
     * Get the list of regular customers for the Blade view.
     */
    public function getCustomersData()
    {
        if (!$this->reportData) {
            return collect();
        }

        return $this->reportData['customers'];
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
                                ->placeholder('Contoh: Laporan Pelanggan Tetap Juli 2025')
                                ->default(function () {
                                    if ($this->reportData) {
                                        return 'Laporan Pelanggan Tetap ' . $this->reportData['startDate'] . ' - ' . $this->reportData['endDate'];
                                    }
                                    return 'Laporan Pelanggan Tetap';
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
                            ->body('Tidak ada data laporan. Silakan pilih rentang tanggal dan kriteria terlebih dahulu.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $reportData = [
                            'name' => $data['report_name'],
                            'start_date' => $this->data['start_date'],
                            'end_date' => $this->data['end_date'],
                            'kriteria_minimum_pesanan' => $this->data['kriteria_minimum_pesanan'] ?? 3,
                        ];

                        $pdf = RegularCustomerReportService::generate($reportData);

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
                ->disabled(fn() => !$this->reportData || $this->reportData['qualifiedCustomers'] === 0)
                ->visible(fn() => $this->reportData !== null),
        ];
    }
}
