<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderPackageResource\Pages;
use App\Filament\Resources\OrderPackageResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\OrderPackage;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderPackageResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = OrderPackage::class;

    protected static ?string $title = 'Paket Pesanan';

    protected static ?string $icon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->required()
                            ->options([
                                'Kiloan' => 'Kiloan',
                                'Karpet' => 'Karpet',
                                'Lembaran' => 'Lembaran',
                                'Satuan' => 'Satuan',
                            ])
                            ->native(false)
                            ->reactive(),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->disabled(fn($get) => $get('type') === null)
                            ->postfix(function ($get) {
                                return match ($get('type')) {
                                    'Kiloan' => 'Per Kilo',
                                    'Karpet' => 'Per Luas (m²)',
                                    'Lembaran' => 'Per Lembar',
                                    'Satuan' => 'Per Item',
                                    default => null,
                                };
                            })
                            ->reactive(),
                    ]),
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
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('Rp. '),
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
            'index' => Pages\ListOrderPackages::route('/'),
            'create' => Pages\CreateOrderPackage::route('/create'),
            'edit' => Pages\EditOrderPackage::route('/{record}/edit'),
        ];
    }
}
