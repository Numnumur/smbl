<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
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

    protected static ?string $model = User::class;

    protected static ?string $title = 'Pelanggan';

    protected static ?string $icon = 'heroicon-o-users';

    protected static ?string $group = 'Manajemen Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Group::make()
                                    ->relationship('customer')
                                    ->schema([
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
                                    ])
                            ])->columnSpan(['lg' => 2]),
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Placeholder::make('email')
                                            ->content(fn($record): string => $record->email),
                                    ]),
                                // Section::make()
                                //     ->schema([
                                //         Forms\Components\Select::make('roles')
                                //             ->relationship('roles', 'name')
                                //             ->preload()
                                //             ->searchable(),
                                //     ]),
                            ])->columnSpan(['lg' => 1]),
                    ])->columns(3)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::where('name', '!=', 'Admin'))
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
            ->defaultSort('created_at', 'desc')
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

    public static function getCustomerForm(): array
    {
        return [
            Group::make()
                ->relationship('customer')
                ->schema([
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
                ])->columns()
        ];
    }

    public static function getUserForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),
            // Forms\Components\Select::make('roles')
            //     ->relationship('roles', 'name')
            //     ->preload()
            //     ->searchable()
            //     ->default('2'),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->unique()
                ->email()
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()
                ->required()
                ->revealable()
                ->maxLength(255),
        ];
    }
}
