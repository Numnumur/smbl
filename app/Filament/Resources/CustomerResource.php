<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = Customer::class;

    protected static ?string $title = 'Pelanggan';

    protected static ?string $icon = 'heroicon-o-users';

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
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp(WA)')
                            ->maxLength(255)
                            ->rules([
                                'required',
                                'regex:/^(08|\+62)([0-9\s\-]{6,15})$/',
                            ])
                            ->validationMessages([
                                'required' => 'Nomor WhatsApp wajib diisi.',
                                'regex' => 'Nomor WhatsApp harus diawali dengan 08 atau +62, dan hanya boleh mengandung angka, spasi, atau tanda strip (-).',
                            ]),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->columnSpanFull()
                            ->maxLength(300),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->maxLength(300),
                    ])->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('Nomor WhatsApp(WA)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->wrap(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->wrap(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
