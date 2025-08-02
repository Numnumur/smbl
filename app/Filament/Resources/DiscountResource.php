<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\Discount;
use App\Models\OrderPackage;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Illuminate\Support\Carbon;

class DiscountResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = Discount::class;

    protected static ?string $title = 'Diskon';

    protected static ?string $icon = 'heroicon-o-receipt-percent';

    protected static ?string $group = 'Manajemen Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('order_package_id')
                            ->label('Paket Pesanan')
                            ->relationship('orderPackage', 'name')
                            ->required()
                            ->options(OrderPackage::all()->pluck('name', 'id'))
                            ->searchable()
                            ->native(false),
                        Forms\Components\Select::make('type')
                            ->label('Tipe Diskon')
                            ->required()
                            ->options([
                                'Persentase' => 'Persentase',
                                'Langsung' => 'Langsung',
                            ])
                            ->native(false)
                            ->reactive(),
                        Forms\Components\TextInput::make('value')
                            ->label('Diskon')
                            ->required()
                            ->numeric()
                            ->disabled(fn($get) => $get('type') === null)
                            ->prefix(fn($get) => $get('type') === 'Langsung' ? 'Rp.' : null)
                            ->postfix(fn($get) => $get('type') === 'Persentase' ? '%' : null)
                            ->reactive(),
                        Section::make()
                            ->description('Masa berlaku diskon')
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Dari Tanggal')
                                    ->native(false)
                                    ->required()
                                    ->disabled(function ($get) {
                                        return $get('type') === null;
                                    })
                                    ->minDate(Carbon::today())
                                    ->reactive()
                                    ->rules(['after_or_equal:today'])
                                    ->validationMessages([
                                        'after_or_equal' => 'Tanggal mulai tidak boleh tanggal lampau.',
                                    ]),
                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Hingga Tanggal')
                                    ->native(false)
                                    ->required()
                                    ->afterOrEqual('start_date')
                                    ->minDate(fn(callable $get) => $get('start_date'))
                                    ->reactive()
                                    ->disabled(fn($get) => $get('type') === null)
                                    ->helperText('Tidak bisa lebih kecil dari tanggal awal')
                                    ->rules(['after_or_equal:start_date'])
                                    ->validationMessages([
                                        'after_or_equal' => 'Tanggal hingga tidak boleh lebih awal dari tanggal mulai.',
                                    ]),
                            ])->columns(),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'Langsung') {
                            return 'Rp ' . number_format($state, 0, ',', '.');
                        }

                        if ($record->type === 'Persentase') {
                            return $state . '%';
                        }

                        return $state;
                    }),
                Tables\Columns\TextColumn::make('orderPackage.name')
                    ->label('Paket Pesanan'),
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
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('type')
                            ->label('Tipe'),
                        TextEntry::make('value')
                            ->label('Nilai')
                            ->prefix(fn($record) => $record->type === 'Langsung' ? 'Rp. ' : null)
                            ->suffix(fn($record) => $record->type === 'Persentase' ? ' %' : null)
                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                        TextEntry::make('start_date')
                            ->label('Dari Tanggal')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('j F Y'))
                            ->extraAttributes(['class' => 'text-center']),
                        TextEntry::make('end_date')
                            ->label('Sampai')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('j F Y'))
                            ->extraAttributes(['class' => 'text-center']),
                        TextEntry::make('orderPackage.name')
                            ->label('Paket Pesanan'),
                    ])->inlineLabel(),
            ]);
    }
}
