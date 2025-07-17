<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = Expense::class;

    protected static ?string $title = 'Pengeluaran';

    protected static ?string $icon = 'heroicon-o-banknotes';

    protected static ?string $group = 'Transaksi';

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
                    ->label('Keperluan'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y')),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('Rp. '),
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
            ->defaultSort('date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
