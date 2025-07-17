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
                        ])
                        ->native(false)
                        ->required()
                        ->default('keuangan pemasukan'),
                    Section::make()
                        ->description('Rentang Waktu Data')
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('Dari Tanggal')
                                ->native(false)
                                ->required()
                                ->reactive()
                                ->default('2025-6-1'),
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
                                ->default('2025-6-30'),
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
                })
        ];
    }
}
