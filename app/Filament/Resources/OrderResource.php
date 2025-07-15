<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPackage;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
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

    protected static ?string $group = 'Transaksi';

    public static function form(Form $form): Form
    {
        $recalculateTotalPrice = function ($state, callable $get, callable $set) {
            $type = $get('type');
            $price = (float) $get('price');
            $status = $get('status');

            $totalBeforeDiscount = 0;

            if ($type === 'Kiloan') {
                $totalBeforeDiscount = $get('weight') * $price;
            } elseif ($type === 'Karpet') {
                $length = $get('length');
                $width = $get('width');
                $area = ($length / 100) * ($width / 100);
                $totalBeforeDiscount = $area * $price;
            } elseif (in_array($type, ['Satuan', 'Lembaran'])) {
                $totalBeforeDiscount = $get('quantity') * $price;
            }

            if ($status == 'Baru') {
                $orderPackage = \App\Models\OrderPackage::where('name', $get('order_package'))->first();
                if ($orderPackage) {
                    $today = now()->toDateString();
                    $discount = \App\Models\Discount::where('order_package_id', $orderPackage->id)
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)
                        ->first();

                    if ($discount) {
                        $set('discount_name', $discount->name);
                        $set('discount_type', $discount->type);
                        $set('discount_value', $discount->value);
                    } else {
                        $set('discount_name', null);
                        $set('discount_type', null);
                        $set('discount_value', null);
                    }
                }

                return;
            }

            if ($status !== 'Selesai Diproses') {
                return;
            }

            $discountType = $get('discount_type');
            $discountValue = (float) $get('discount_value');

            $totalAfterDiscount = $totalBeforeDiscount;

            if ($discountType && $discountValue) {
                if ($discountType === 'Persentase') {
                    $totalAfterDiscount -= ($totalBeforeDiscount * $discountValue / 100);
                } elseif ($discountType === 'Langsung') {
                    $totalAfterDiscount -= $discountValue;
                }
            }

            $set('total_price', max(round($totalBeforeDiscount), 0));
            $set('total_price_after_discount', max(round($totalAfterDiscount), 0));
        };



        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\DateTimePicker::make('entry_date')
                            ->label('Tanggal Pesanan Masuk')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\DateTimePicker::make('exit_date')
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
                            ->disabled()
                            ->dehydrated(true)
                            ->visible(fn(callable $get) => $get('status') !== 'Baru'),

                        Forms\Components\TextInput::make('discount_name')
                            ->label('Nama Diskon')
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\Select::make('discount_type')
                            ->label('Tipe Diskon')
                            ->options([
                                'Persentase' => 'Persentase',
                                'Langsung' => 'Langsung',
                            ])
                            ->native(false)
                            ->reactive()
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\TextInput::make('discount_value')
                            ->label('Jumlah Diskon')
                            ->numeric()
                            ->prefix(fn($get) => $get('discount_type') === 'Langsung' ? 'Rp. ' : null)
                            ->postfix(fn($get) => $get('discount_type') === 'Persentase' ? '%' : null)
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\TextInput::make('total_price_after_discount')
                            ->label('Total Harga Setelah Diskon')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->disabled()
                            ->dehydrated(true)
                            ->visible(fn(callable $get) => $get('status') !== 'Baru'),
                    ])->columns(),

                Section::make()
                    ->visible(fn(callable $get) => $get('status') === 'Selesai')
                    ->schema([
                        FileUpload::make('retrieval_proof')
                            ->label('Bukti Pengambilan')
                            ->openable()
                            ->maxSize(2048)
                            ->visibility('public')
                            ->disk('public')
                            ->directory('proof/retrieval')
                            ->imageResizeMode('contain')
                            ->removeUploadedFileButtonPosition('center bottom')
                            ->uploadButtonPosition('center bottom')
                            ->uploadProgressIndicatorPosition('center bottom')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg', 'image/webp'])
                            ->extraAttributes(['class' => 'w-48 h-auto'])
                            ->disabled(false),
                        FileUpload::make('delivery_proof')
                            ->label('Bukti Pengantaran')
                            ->openable()
                            ->maxSize(2048)
                            ->visibility('public')
                            ->disk('public')
                            ->directory('proof/delivery')
                            ->imageResizeMode('contain')
                            ->removeUploadedFileButtonPosition('center bottom')
                            ->uploadButtonPosition('center bottom')
                            ->uploadProgressIndicatorPosition('center bottom')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg', 'image/webp'])
                            ->extraAttributes(['class' => 'w-48 h-auto'])
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
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Baru' => 'info',
                        'Selesai Diproses' => 'warning',
                        'Selesai' => 'success',
                    }),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Pesanan Masuk')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y H:i:s')),
                Tables\Columns\TextColumn::make('exit_date')
                    ->label('Tanggal Pesanan Selesai')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y H:i:s')),
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
            ->defaultSort('entry_date', 'desc')
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
