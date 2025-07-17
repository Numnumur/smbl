<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

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
                        ->columnSpanFull(),
                    Select::make('type')
                        ->label('Tipe')
                        ->options([
                            'keuangan pemasukan' => 'Keuangan - Pemasukan',
                            'keuangan pengeluaran' => 'Keuangan - Pengeluaran',
                        ])
                        ->native(false)
                        ->required(),
                    Section::make()
                        ->description('Rentang Waktu Data')
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('Dari Tanggal')
                                ->native(false)
                                ->required()
                                ->reactive(),
                            DatePicker::make('end_date')
                                ->label('Hingga Tanggal')
                                ->native(false)
                                ->required()
                                ->afterOrEqual('start_date')
                                ->reactive()
                                ->rules(['after_or_equal:start_date'])
                                ->validationMessages([
                                    'after_or_equal' => 'Tanggal hingga tidak boleh lebih awal dari tanggal mulai.',
                                ]),
                        ])->columns(),
                ])
                ->action(function (array $data) {
                    // kode
                }),
        ];
    }
}
