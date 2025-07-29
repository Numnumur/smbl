<?php

namespace App\Filament\Pages;

use App\Models\PickupDelivery;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Services\Reports\FinanceIncomeReportService;
use App\Services\Reports\FinanceExpenseReportService;
use App\Services\Reports\RegularCustomerReportService;
use App\Services\Reports\CustomerReportService;
use App\Services\Reports\OrderWorkReportService;
use App\Services\Reports\DiscountReportService;
use App\Services\Reports\CustomerOrderEntryExitReportService;
use App\Services\Reports\PickupDeliveryReportService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action as FormAction;

class Dashboard extends BaseDashboard
{
    public function getHeaderActions(): array
    {
        $user = auth()->user();

        if ($user->hasRole('panel_user')) {
            $customer = $user->customer;

            $missingFields = collect([
                'Alamat' => $customer?->address,
                'Nomor WhatsApp' => $customer?->whatsapp,
            ])->filter(fn($value) => blank($value));

            $isIncomplete = $missingFields->isNotEmpty();

            return [
                Action::make('permintaan_antar_jemput')
                    ->label('Permintaan Antar Jemput')
                    ->icon('heroicon-o-archive-box')
                    ->color('primary')
                    ->modalHeading('Buat Permintaan Antar Jemput')
                    ->modalWidth('lg')
                    ->form(function () use ($missingFields) {
                        $formComponents = [];

                        if ($missingFields->isNotEmpty()) {
                            $formComponents[] = Placeholder::make('data_belum_lengkap')
                                ->label('Data Anda Belum Lengkap')
                                ->content('Anda harus melengkapi data terlebih dahulu sebelum membuat permintaan antar jemput. Data yang belum ada: ' . $missingFields->keys()->join(', ') . '.')
                                ->extraAttributes(['class' => 'text-red-600 font-medium'])
                                ->columnSpanFull()
                                ->hintAction(
                                    FormAction::make('lengkapi')
                                        ->label('Lengkapi Sekarang')
                                        ->icon('heroicon-o-arrow-right')
                                        ->url(route('filament.admin.settings.pages.address-and-whatsapp'))
                                );
                        } else {
                            $formComponents[] = Section::make()
                                ->description('Wajib Diisi')
                                ->schema([
                                    Select::make('type')
                                        ->label('Tipe Permintaan')
                                        ->options([
                                            'Antar' => 'Antar',
                                            'Jemput' => 'Jemput',
                                            'Antar dan Jemput' => 'Antar dan Jemput',
                                        ])
                                        ->native(false)
                                        ->required()
                                        ->columnSpanFull(),
                                    DateTimePicker::make('date_and_time')
                                        ->label('Tanggal dan Waktu')
                                        ->native(false)
                                        ->seconds(false)
                                        ->required(),
                                ])->columns();

                            $formComponents[] = Section::make()
                                ->description('Opsional (Boleh Dikosongkan)')
                                ->schema([
                                    Textarea::make('customer_note')
                                        ->label('Catatan')
                                        ->columnSpanFull()
                                        ->maxLength(300),
                                ])->columns();
                        }

                        return $formComponents;
                    })
                    ->modalSubmitAction($isIncomplete ? false : null)
                    ->modalSubmitActionLabel('Kirim')
                    ->modalCancelActionLabel('Tutup')
                    ->action(function (array $data) use ($user, $isIncomplete) {
                        if ($isIncomplete) {
                            return;
                        }

                        $customer = $user->customer;

                        PickupDelivery::create([
                            'type' => $data['type'],
                            'status' => 'Menunggu Konfirmasi',
                            'date_and_time' => $data['date_and_time'],
                            'customer_note' => $data['customer_note'],
                            'user_id' => $user->id,
                            'customer_id' => $customer?->id,
                        ]);

                        $this->getSavedNotification()->send();
                    }),
            ];
        }

        if ($user->hasRole('super_admin')) {
            return [
                Action::make('laporan')
                    ->label('Laporan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('primary')
                    ->modalSubmitActionLabel('Buat')
                    ->modalHeading('Buat Laporan')
                    ->modalWidth('lg')
                    ->form([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->columnSpanFull()
                            ->default('Contoh Laporan'),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'keuangan pemasukan' => 'Keuangan - Pemasukan',
                                'keuangan pengeluaran' => 'Keuangan - Pengeluaran',
                                'pelanggan tetap' => 'Pelanggan Tetap',
                                'pesanan pelanggan' => 'Pesanan Pelanggan',
                                'pengerjaan pesanan' => 'Pengerjaan Pesanan',
                                'pemberian diskon' => 'Pemberian Diskon',
                                'detail keluar masuk' => 'Keluar Masuk Pesanan',
                                'permintaan antar jemput' => 'Permintaan Antar Jemput',
                            ])
                            ->reactive()
                            ->native(false)
                            ->required()
                            ->default('keuangan pemasukan'),
                        TextInput::make('kriteria_minimum_pesanan')
                            ->label('Kriteria Minimum Pesanan')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn($get) => $get('type') === 'pelanggan tetap')
                            ->required(fn($get) => $get('type') === 'pelanggan tetap')
                            ->reactive()
                            ->columnSpanFull(),
                        Section::make()
                            ->description('Rentang Waktu Data')
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Dari Tanggal')
                                    ->native(false)
                                    ->required()
                                    ->reactive()
                                    ->default('2025-7-1'),
                                DatePicker::make('end_date')
                                    ->label('Hingga Tanggal')
                                    ->native(false)
                                    ->required()
                                    ->afterOrEqual('start_date')
                                    ->reactive()
                                    ->rules(['after_or_equal:start_date'])
                                    ->validationMessages([
                                        'after_or_equal' => 'Tanggal hingga tidak boleh lebih awal dari tanggal mulai.',
                                    ])
                                    ->default('2025-7-31'),
                            ])->columns(),
                    ])
                    ->action(function (array $data) {
                        $map = [
                            'keuangan pemasukan' => FinanceIncomeReportService::class,
                            'keuangan pengeluaran' => FinanceExpenseReportService::class,
                            'pelanggan tetap' => RegularCustomerReportService::class,
                            'pesanan pelanggan' => CustomerReportService::class,
                            'pengerjaan pesanan' => OrderWorkReportService::class,
                            'pemberian diskon' => DiscountReportService::class,
                            'detail keluar masuk' => CustomerOrderEntryExitReportService::class,
                            'permintaan antar jemput' => PickupDeliveryReportService::class
                        ];

                        $type = $data['type'];

                        if (in_array($type, ['keuangan pemasukan', 'keuangan pengeluaran', 'pelanggan tetap'])) {
                            $pdf = $map[$type]::generate($data);
                        } else {
                            $pdf = $map[$type]::generatePdf(
                                $data['name'],
                                Carbon::parse($data['start_date']),
                                Carbon::parse($data['end_date']),
                            );
                        }

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['name'] . '.pdf');

                        $this->getSavedNotification2()->send();
                    }),
            ];
        }

        return [];
    }


    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title('Permintaan Berhasil Dibuat');
    }

    protected function getSavedNotification2(): Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan Berhasil Dibuat');
    }
}
