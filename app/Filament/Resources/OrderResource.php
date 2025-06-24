<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPackage;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = Order::class;

    protected static ?string $title = 'Pesanan';

    protected static ?string $icon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        $recalculateTotalPrice = function ($state, callable $get, callable $set) {
            $type = $get('type');
            $price = (float) $get('price');
            $total = 0;

            if ($type === 'Kiloan') {
                $total = $get('weight') * $price;
            } elseif ($type === 'Karpet') {
                $length = $get('length');
                $width = $get('width');
                $area = ($length / 100) * ($width / 100);
                $total = $area * $price;
            } elseif (in_array($type, ['Satuan', 'Lembaran'])) {
                $total = $get('quantity') * $price;
            }

            $orderPackage = \App\Models\OrderPackage::where('name', $get('order_package'))->first();
            if ($orderPackage) {
                $today = now()->toDateString();
                $discount = \App\Models\Discount::where('order_package_id', $orderPackage->id)
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->first();

                if ($discount) {
                    if ($discount->type === 'Persentase') {
                        if ($discount->minimum === null || $total >= $discount->minimum) {
                            $total -= ($total * $discount->value / 100);
                        }
                    } elseif ($discount->type === 'Langsung') {
                        if ($discount->minimum === null || $total >= $discount->minimum) {
                            $total -= $discount->value;
                        }
                    }
                }
            }

            $set('total_price', max(round($total), 0));
        };

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('entry_date')
                            ->label('Tanggal Pesanan Masuk')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\DatePicker::make('exit_date')
                            ->label('Tanggal Pesanan Selesai')
                            ->native(false)
                            ->disabled(fn(callable $get) => in_array($get('status'), ['Baru', 'Selesai Diproses']))
                            ->required(fn(callable $get) => $get('status') === 'Selesai'),
                        Forms\Components\Select::make('customer_id')
                            ->label('Pelanggan')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('order_package')
                            ->label('Paket Pesanan')
                            ->options(OrderPackage::all()->pluck('name', 'name'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($recalculateTotalPrice) {
                                $package = \App\Models\OrderPackage::where('name', $state)->first();
                                if ($package) {
                                    $set('type', $package->type);
                                    $set('price', $package->price);
                                } else {
                                    $set('type', null);
                                    $set('price', null);
                                }

                                $recalculateTotalPrice($state, $get, $set);
                            })
                            ->debounce(800),
                        Forms\Components\Select::make('status')
                            ->label('Status Pesanan')
                            ->required()
                            ->options([
                                'Baru' => 'Baru',
                                'Selesai Diproses' => 'Selesai Diproses',
                                'Selesai' => 'Selesai',
                            ])
                            ->native(false)
                            ->default('Baru')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('exit_date', $state === 'Selesai' ? now() : null);
                            })
                            ->debounce(800),
                    ])->columns(),

                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('type')
                            ->label('Tipe')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(true)
                            ->reactive(),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp. ')
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\TextInput::make('length')
                            ->label('Panjang')
                            ->numeric()
                            ->postfix('cm')
                            ->disabled(fn($get) => in_array($get('status'), ['Baru', 'Selesai']))
                            ->required(fn($get) => $get('status') !== 'Baru' && $get('type') === 'Karpet')
                            ->visible(fn($get) => $get('status') !== 'Baru' && $get('type') === 'Karpet')
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(800),
                        Forms\Components\TextInput::make('width')
                            ->label('Lebar')
                            ->numeric()
                            ->postfix('cm')
                            ->disabled(fn($get) => in_array($get('status'), ['Baru', 'Selesai']))
                            ->required(fn($get) => $get('status') !== 'Baru' && $get('type') === 'Karpet')
                            ->visible(fn($get) => $get('status') !== 'Baru' && $get('type') === 'Karpet')
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(800),
                        Forms\Components\TextInput::make('weight')
                            ->label('Berat')
                            ->numeric()
                            ->postfix('kg')
                            ->disabled(fn($get) => in_array($get('status'), ['Baru', 'Selesai']))
                            ->required(fn($get) => $get('status') !== 'Baru' && $get('type') === 'Kiloan')
                            ->visible(fn($get) => $get('status') !== 'Baru' && $get('type') === 'Kiloan')
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(800),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->postfix('item')
                            ->disabled(fn($get) => in_array($get('status'), ['Baru', 'Selesai']))
                            ->required(fn($get) => $get('status') !== 'Baru' && in_array($get('type'), ['Lembaran', 'Satuan']))
                            ->visible(fn($get) => $get('status') !== 'Baru' && in_array($get('type'), ['Lembaran', 'Satuan']))
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(800),
                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Harga')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->disabled()
                            ->dehydrated(true)
                            ->visible(fn(callable $get) => $get('status') !== 'Baru'),
                    ])->columns(),

                Section::make()
                    ->schema([
                        Forms\Components\Textarea::make('retrieval_proof')
                            ->label('Bukti Pengambilan')
                            ->visible(fn(callable $get) => $get('status') === 'Selesai')
                            ->disabled(false),
                        Forms\Components\Textarea::make('delivery_proof')
                            ->label('Bukti Pengantaran')
                            ->visible(fn(callable $get) => $get('status') === 'Selesai')
                            ->disabled(false),
                    ])->columns(),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Pesanan Masuk')
                    ->date('d F Y'),
                Tables\Columns\TextColumn::make('exit_date')
                    ->label('Tanggal Pesanan Selesai')
                    ->date('d F Y'),
                Tables\Columns\TextColumn::make('order_package')
                    ->label('Paket Pesanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Harga')
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
