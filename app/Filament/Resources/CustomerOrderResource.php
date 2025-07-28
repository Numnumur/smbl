<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerOrderResource\Pages;
use App\Filament\Resources\CustomerOrderResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\Order as CustomerOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Group;
use Carbon\Carbon;

class CustomerOrderResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = CustomerOrder::class;

    protected static ?string $title = 'Riwayat Pesanan';

    protected static ?string $icon = 'heroicon-o-shopping-bag';

    protected static ?string $group = '';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('panel_user');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::getEloquentQuery()
                    ->where('customer_id', auth()->user()->customer?->id ?? 0)
                    ->orderByDesc('entry_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_package')
                    ->label('Paket Pesanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Baru' => 'info',
                        'Selesai Diproses' => 'warning',
                        'Selesai' => 'success',
                    }),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return null;

                        $date = \Carbon\Carbon::parse($state)->locale('id');
                        $formattedDate = $date->translatedFormat('j F Y');
                        $diffForHumans = $date->diffForHumans(null, false, false, 2);

                        return "$formattedDate ($diffForHumans)";
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerOrders::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Sinar Laundry')
                    ->schema([
                        TextEntry::make('store_address')
                            ->label('')
                            ->default("Jl. Kasturi 2, RT.038/RW.006, Syamsudin Noor, Kec. Landasan Ulin, Kota Banjar Baru, Kalimantan Selatan 70724")
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-center']),
                        TextEntry::make('store_contact')
                            ->label('')
                            ->default("Whatsapp: +6285158803862")
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-center']),
                    ])->extraAttributes(['class' => 'text-center']),

                InfolistSection::make()
                    ->schema([
                        TextEntry::make('entry_date')
                            ->label('Tanggal Masuk')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y H:i'))
                            ->extraAttributes(['class' => 'text-center']),

                        TextEntry::make('exit_date')
                            ->label('Tanggal Selesai')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y H:i'))
                            ->extraAttributes(['class' => 'text-center']),
                    ])
                    ->columns(2),


                Group::make([
                    InfolistSection::make('Pesanan')
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->columnSpanFull()
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'Baru' => 'info',
                                    'Selesai Diproses' => 'warning',
                                    'Selesai' => 'success',
                                }),

                            TextEntry::make('order_package')
                                ->label('Paket')
                                ->columnSpanFull(),

                            TextEntry::make('type')
                                ->label('Tipe')
                                ->columnSpanFull(),

                            TextEntry::make('price')
                                ->label('Tarif')
                                ->prefix('Rp. ')
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                                ->columnSpanFull(),
                        ])
                        ->extraAttributes(['class' => 'text-center'])
                        ->columns(1)
                        ->columnSpan(1),

                    InfolistSection::make('Detail')
                        ->schema([
                            TextEntry::make('weight')
                                ->label('Berat')
                                ->suffix(' kg')
                                ->visible(fn($record) => $record->type === 'Kiloan'),

                            TextEntry::make('length')
                                ->label('Ukuran')
                                ->visible(fn($record) => $record->type === 'Karpet')
                                ->formatStateUsing(function ($state, $record) {
                                    $length = $record->length / 100;
                                    $width = $record->width / 100;
                                    $area = $length * $width;

                                    return number_format($length, 2) . ' x ' . number_format($width, 2) . ' = ' . number_format($area, 2) . ' m²';
                                }),

                            TextEntry::make('quantity')
                                ->label('Jumlah')
                                ->suffix(' lembar')
                                ->visible(fn($record) => $record->type === 'Lembaran'),

                            TextEntry::make('quantity')
                                ->label('Jumlah')
                                ->suffix(' item')
                                ->visible(fn($record) => $record->type === 'Satuan'),

                            TextEntry::make('total_price_before_discount')
                                ->label('Total Tagihan')
                                ->visible(fn($record) => $record->discount_value > 0)
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                                ->prefix('Rp. '),

                            TextEntry::make('discount_value')
                                ->label(fn($record) => "Diskon {$record->discount_type}")
                                ->helperText(fn($record) => $record->discount_name)
                                ->visible(fn($record) => $record->discount_value > 0)
                                ->prefix(fn($record) => $record->discount_type === 'Langsung' ? 'Rp. ' : null)
                                ->suffix(fn($record) => $record->discount_type === 'Persentase' ? ' %' : null)
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                            TextEntry::make('total_price')
                                ->label(fn($record) => $record->discount_value > 0 ? 'Setelah Diskon' : 'Total Tagihan')
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                                ->prefix('Rp. ')
                                ->color('success'),
                        ])
                        ->extraAttributes(['class' => 'text-center'])
                        ->columns(1)
                        ->columnSpan(1),

                ])->columnSpanFull()->columns(2),

                InfolistSection::make()
                    ->schema([
                        TextEntry::make('thanks_note')
                            ->label('')
                            ->default("Terima kasih karena telah menggunakan jasa kami.\nJika ada pertanyaan/masukan/komplain silahkan hubungi nomor WhatsApp yang tertera di atas.")
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-center']),
                    ]),
            ]);
    }
}
