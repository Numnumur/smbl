<?php

namespace App\Filament\Pages;

use App\Models\Order;
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
use Illuminate\Support\Carbon;

class Dashboard extends BaseDashboard
{
    public function getHeaderActions(): array
    {
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
                    if ($data['type'] === 'keuangan pemasukan') {
                        $pdf = FinanceIncomeReportService::generate($data);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['name'] . '.pdf');
                    }

                    if ($data['type'] === 'keuangan pengeluaran') {
                        $pdf = FinanceExpenseReportService::generate($data);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['name'] . '.pdf');
                    }

                    if ($data['type'] === 'pelanggan tetap') {
                        $pdf = RegularCustomerReportService::generate($data);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['name'] . '.pdf');
                    }

                    if ($data['type'] === 'pesanan pelanggan') {
                        $pdf = CustomerReportService::generatePdf(
                            $data['name'],
                            Carbon::parse($data['start_date']),
                            Carbon::parse($data['end_date']),
                        );

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['name'] . '.pdf');
                    }

                    if ($data['type'] === 'pengerjaan pesanan') {
                        $pdf = OrderWorkReportService::generatePdf(
                            $data['name'],
                            Carbon::parse($data['start_date']),
                            Carbon::parse($data['end_date']),
                        );

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['name'] . '.pdf');
                    }

                    if ($data['type'] === 'pemberian diskon') {
                        $pdf = DiscountReportService::generatePdf(
                            $data['name'],
                            Carbon::parse($data['start_date']),
                            Carbon::parse($data['end_date']),
                        );

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf;
                        }, $data['name'] . '.pdf');
                    }
                })
        ];
    }
}
