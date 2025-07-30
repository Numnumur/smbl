<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PickupDeliveryResource\Pages;
use App\Filament\Resources\PickupDeliveryResource\RelationManagers;
use App\Models\PickupDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Helper\ResourceCustomizing;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;

class PickupDeliveryResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = PickupDelivery::class;

    protected static ?string $title = 'Antar Jemput';

    protected static ?string $icon = 'heroicon-o-archive-box';

    protected static ?string $group = '';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Placeholder::make('name')
                            ->label('Pelanggan')
                            ->content(fn($record): string => $record?->customer?->user?->name ?? '-'),

                        Placeholder::make('date_and_time')
                            ->label('Tanggal dan Waktu')
                            ->content(fn($record): string => $record?->date_and_time ?? '-'),

                        Placeholder::make('type')
                            ->label('Tipe')
                            ->content(fn($record): string => $record?->type ?? '-'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Menunggu Konfirmasi' => 'Menunggu Konfirmasi',
                                'Sudah Dikonfirmasi' => 'Sudah Dikonfirmasi',
                                'Selesai' => 'Selesai',
                                'Ditolak' => 'Ditolak',
                            ])
                            ->native(false)
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),

                        Textarea::make('laundry_note')
                            ->label('Alasan Penolakan')
                            ->columnSpanFull()
                            ->maxLength(300)
                            ->visible(fn(Forms\Get $get) => $get('status') === 'Ditolak')
                            ->required(fn(Forms\Get $get) => $get('status') === 'Ditolak'),

                        Placeholder::make('customer_note')
                            ->label('Catatan Dari Pelanggan')
                            ->content(fn($record): string => $record?->customer_note ?? '-'),
                    ])->inlineLabel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.user.name')
                    ->label('Pelanggan'),

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

                Tables\Columns\IconColumn::make('whatsapp_notified_customer')
                    ->label('Notifikasi WA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->whatsapp_notified_customer ? 'Terkirim' : 'Belum Terkirim'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPickupDeliveries::route('/'),
            'edit' => Pages\EditPickupDelivery::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('customer.user.name')
                            ->label('Pelanggan'),
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
                        TextEntry::make('laundry_note')
                            ->label('Alasan Penolakan')
                            ->prose()
                            ->alignJustify()
                            ->visible(fn($record) => $record->status === 'Ditolak'),
                        TextEntry::make('customer_note')
                            ->label('Catatan Dari Pelanggan')
                            ->prose()
                            ->alignJustify(),
                    ])->inlineLabel(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::whereNotIn('status', ['Selesai', 'Ditolak'])->count();
    }
}
