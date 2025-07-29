<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerPickupDeliveryResource\Pages;
use App\Filament\Resources\CustomerPickupDeliveryResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\PickupDelivery as CustomerPickupDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;

class CustomerPickupDeliveryResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = CustomerPickupDelivery::class;

    protected static ?string $title = 'Riwayat Antar Jemput';

    protected static ?string $icon = 'heroicon-o-archive-box';

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
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date_and_time')
                    ->label('Tanggal dan Waktu')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('j F Y H:i') : '-'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Menunggu Konfirmasi' => 'info',
                        'Sudah Dikonfirmasi' => 'warning',
                        'Selesai' => 'success',
                        'Ditolak' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('customer_note')
                    ->label('Catatan')
                    ->formatStateUsing(fn($state) => $state ?: '-'),

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
                Tables\Actions\DeleteAction::make()
                    ->label('Batal')
                    ->modalHeading('Batalkan Permintaan Antar Jemput')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->visible(fn($record) => $record->status === 'Menunggu Konfirmasi'),
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
            'index' => Pages\ListCustomerPickupDeliveries::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('date_and_time')
                            ->label('Tanggal dan Waktu'),
                        TextEntry::make('type')
                            ->label('Tipe'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->colors([
                                'info' => static fn($state): bool => $state === 'Menunggu Konfirmasi',
                                'warning' => static fn($state): bool => $state === 'Sudah Dikonfirmasi',
                                'success' => static fn($state): bool => $state === 'Selesai',
                                'danger' => static fn($state): bool => $state === 'Ditolak',
                            ]),
                        TextEntry::make('customer_note')
                            ->label('Catatan')
                            ->prose()
                            ->alignJustify(),
                    ])->inlineLabel(),
            ]);
    }
}
