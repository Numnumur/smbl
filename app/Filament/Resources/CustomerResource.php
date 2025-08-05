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
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Tables\Enums\ActionsPosition;

class CustomerResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = User::class;

    protected static ?string $title = 'Pelanggan';

    protected static ?string $icon = 'heroicon-o-users';

    protected static ?string $group = 'Manajemen Data';

    protected static ?int $navigationSort = 21;

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
                                            ->label('Nomor WhatsApp (WA)')
                                            ->maxLength(15)
                                            ->helperText('Contoh nomor WA: 628xxxxxxxxxx')
                                            ->prefix('+')
                                            ->rules([
                                                'regex:/^62[0-9]{7,13}$/',
                                            ])
                                            ->validationMessages([
                                                'regex' => 'Nomor WhatsApp harus diawali dengan 62 dan hanya boleh mengandung angka tanpa spasi atau karakter lain.',
                                            ])
                                            ->inputMode('numeric')
                                            ->extraAttributes([
                                                'inputmode' => 'numeric',
                                                'pattern' => '[0-9]*',
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
                            ])->columnSpan(['lg' => 1]),
                    ])->columns(3)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super_admin');
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.whatsapp')
                    ->label('Nomor WhatsApp (WA)')
                    ->searchable()
                    ->prefix('+')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('customer.address')
                    ->label('Alamat')
                    ->wrap()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('customer.note')
                    ->label('Catatan')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('customer.created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customer.updated_at')
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
            ], position: ActionsPosition::BeforeColumns);
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
                        ->label('Nomor WhatsApp (WA)')
                        ->maxLength(15)
                        ->helperText('Contoh nomor WA: 628xxxxxxxxxx')
                        ->prefix('+')
                        ->rules([
                            'regex:/^62[0-9]{7,13}$/',
                        ])
                        ->validationMessages([
                            'regex' => 'Nomor WhatsApp harus diawali dengan 62 dan hanya boleh mengandung angka tanpa spasi atau karakter lain.',
                        ])
                        ->inputMode('numeric')
                        ->extraAttributes([
                            'inputmode' => 'numeric',
                            'pattern' => '[0-9]*',
                            'x-on:keydown.enter.prevent' => '',
                        ]),
                    Forms\Components\Textarea::make('address')
                        ->label('Alamat')
                        ->columnSpanFull()
                        ->maxLength(300)
                        ->extraAttributes([
                            'x-on:keydown.enter.prevent' => '',
                        ]),
                    Forms\Components\Textarea::make('note')
                        ->label('Catatan')
                        ->columnSpanFull()
                        ->maxLength(300)
                        ->extraAttributes([
                            'x-on:keydown.enter.prevent' => '',
                        ]),
                ])->columns()
        ];
    }

    public static function getUserForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255)
                ->extraAttributes([
                    'x-on:keydown.enter.prevent' => '',
                ]),
            Forms\Components\Select::make('roles')
                ->relationship('roles', 'id')
                ->default(2)
                ->visible(false),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->unique()
                ->email()
                ->required()
                ->maxLength(255)
                ->extraAttributes([
                    'x-on:keydown.enter.prevent' => '',
                ]),
            Forms\Components\TextInput::make('password')
                ->label('Kata Sandi')
                ->password()
                ->required()
                ->revealable()
                ->maxLength(255)
                ->extraAttributes([
                    'x-on:keydown.enter.prevent' => '',
                ]),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
                        ImageEntry::make('profile_image')
                            ->label('Foto Profil')
                            ->circular()
                            ->height(200)
                            ->grow(false)
                            ->extraAttributes([
                                'class' => 'flex justify-center'
                            ]),
                    ]),

                InfolistSection::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('customer.whatsapp')
                            ->label('Nomor WhatsApp')
                            ->prefix('+'),
                        TextEntry::make('customer.address')
                            ->label('Alamat'),
                        TextEntry::make('customer.note')
                            ->label('Catatan')
                            ->prose()
                            ->alignJustify(),
                    ])->inlineLabel(),
            ]);
    }
}
