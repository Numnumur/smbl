<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
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
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Enums\ActionsPosition;

class ExpenseResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = Expense::class;

    protected static ?string $title = 'Pengeluaran';

    protected static ?string $icon = 'heroicon-o-banknotes';

    protected static ?string $group = 'Transaksi';

    protected static ?int $navigationSort = 31;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\Select::make('needs')
                            ->required()
                            ->label('Keperluan')
                            ->columnSpanFull()
                            ->options([
                                'Plastik' => 'Plastik',
                                'Parfum' => 'Parfum',
                                'Listrik' => 'Listrik',
                                'Sabun' => 'Sabun',
                                'Alat' => 'Alat',
                                'Perbaikan' => 'Perbaikan',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->native(false),
                        Forms\Components\TextArea::make('detail')
                            ->label('Detail Tambahan')
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->default(now()),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp.'),
                        FileUpload::make('proof')
                            ->label('Bukti Pengeluaran')
                            ->openable()
                            ->maxSize(2048)
                            ->visibility('public')
                            ->disk('public')
                            ->directory('proof/expense')
                            ->imageResizeMode('contain')
                            ->removeUploadedFileButtonPosition('center bottom')
                            ->uploadButtonPosition('center bottom')
                            ->uploadProgressIndicatorPosition('center bottom')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg', 'image/webp'])
                            ->extraAttributes(['class' => 'w-48 h-auto']),
                    ])->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('needs')
                    ->label('Keperluan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('detail')
                    ->label('Detail Tambahan')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->locale('id')->translatedFormat('j F Y')),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('date', 'desc')
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
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
                        TextEntry::make('needs')
                            ->label('Kebutuhan'),
                        TextEntry::make('detail')
                            ->label('Detail')
                            ->prose()
                            ->alignJustify(),
                        TextEntry::make('date')
                            ->label('Tanggal')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('j F Y'))
                            ->extraAttributes(['class' => 'text-center']),
                        TextEntry::make('price')
                            ->label('Harga')
                            ->prefix('Rp. ')
                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                        ImageEntry::make('proof')
                            ->label('Bukti Pengeluaran')
                            ->height(450),
                    ])->inlineLabel(),
            ]);
    }
}
