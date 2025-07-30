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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use App\Models\WhatsappSetting;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = Order::class;

    protected static ?string $title = 'Pesanan';

    protected static ?string $icon = 'heroicon-o-shopping-bag';

    protected static ?string $group = 'Transaksi';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

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

            if ($discountType && $discountValue) {
                $set('total_price_before_discount', max(round($totalBeforeDiscount), 0));
                $set('total_price', max(round($totalAfterDiscount), 0));
            } else {
                $set('total_price', max(round($totalBeforeDiscount), 0));
            }
        };



        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\DateTimePicker::make('entry_date')
                            ->label('Tanggal Pesanan Masuk')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\DateTimePicker::make('exit_date')
                            ->label('Tanggal Pesanan Selesai')
                            ->native(false)
                            ->disabled(fn(callable $get) => in_array($get('status'), ['Baru', 'Selesai Diproses']))
                            ->required(fn(callable $get) => $get('status') === 'Selesai')
                            ->seconds(false),
                        Forms\Components\Select::make('customer_id')
                            ->label('Pelanggan')
                            ->options(
                                Customer::with('user')->get()
                                    ->mapWithKeys(fn($customer) => [$customer->id => $customer->user->name ?? 'Tidak diketahui'])
                            )
                            ->searchable()
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
                            ->debounce(1200),
                        Forms\Components\Select::make('status')
                            ->label('Status Pesanan')
                            ->required()
                            ->options([
                                'Baru' => 'Baru',
                                'Selesai Diproses' => 'Selesai Diproses',
                                'Selesai' => 'Selesai',
                                'Terkendala' => 'Terkendala',
                            ])
                            ->native(false)
                            ->default('Baru')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('exit_date', $state === 'Selesai' ? now() : null);
                            })
                            ->debounce(1200),
                    ])->columns(),

                Section::make()
                    ->visible(fn(callable $get) => $get('status') === 'Terkendala')
                    ->schema([
                        Textarea::make('laundry_note')
                            ->label('Penyebab Kendala')
                            ->columnSpanFull()
                            ->maxLength(300)
                            ->required(fn(Forms\Get $get) => $get('status') === 'Terkendala'),
                    ]),

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
                            ->required(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && $get('type') === 'Karpet')
                            ->visible(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && $get('type') === 'Karpet')
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(1500),
                        Forms\Components\TextInput::make('width')
                            ->label('Lebar')
                            ->numeric()
                            ->postfix('cm')
                            ->disabled(fn($get) => in_array($get('status'), ['Baru', 'Selesai']))
                            ->required(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && $get('type') === 'Karpet')
                            ->visible(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && $get('type') === 'Karpet')
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(1500),
                        Forms\Components\TextInput::make('weight')
                            ->label('Berat')
                            ->numeric()
                            ->postfix('kg')
                            ->disabled(fn($get) => in_array($get('status'), ['Baru', 'Selesai']))
                            ->required(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && $get('type') === 'Kiloan')
                            ->visible(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && $get('type') === 'Kiloan')
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(1200),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->postfix('item')
                            ->disabled(fn($get) => in_array($get('status'), ['Baru', 'Selesai']))
                            ->required(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && in_array($get('type'), ['Lembaran', 'Satuan']))
                            ->visible(fn($get) => !in_array($get('status'), ['Baru', 'Terkendala']) && in_array($get('type'), ['Lembaran', 'Satuan']))
                            ->reactive()
                            ->afterStateUpdated($recalculateTotalPrice)
                            ->debounce(1200),
                        Forms\Components\TextInput::make('total_price_before_discount')
                            ->label('Total Harga Sebelum Diskon')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->disabled()
                            ->dehydrated(true)
                            ->visible(fn(callable $get) => !in_array($get('status'), ['Baru', 'Terkendala'])),
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
                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Harga')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->disabled()
                            ->disabled()
                            ->dehydrated(true)
                            ->visible(fn(callable $get) => !in_array($get('status'), ['Baru', 'Terkendala'])),
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
            ->poll('20s')
            ->columns([
                Tables\Columns\TextColumn::make('customer.user.name')
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
                        'Terkendala' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Pesanan Masuk')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('j F Y H:i')),
                Tables\Columns\TextColumn::make('order_package')
                    ->label('Paket Pesanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->money('Rp. '),
                Tables\Columns\IconColumn::make('whatsapp_notified')
                    ->label('Notifikasi WA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->whatsapp_notified ? 'Terkirim' : 'Belum Terkirim'),

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
            ->defaultSort('entry_date', 'desc')
            ->actions([
                Tables\Actions\Action::make('kirimWhatsapp')
                    ->label('Kirim Notifikasi WA')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        in_array($record->status, ['Selesai', 'Terkendala']) &&
                            !$record->whatsapp_notified
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $customer = $record->customer;

                        if (!$customer || !$customer->whatsapp) {
                            Log::warning("WhatsApp tidak tersedia untuk customer ID: {$customer?->id}");
                            $record->whatsapp_notified = false;
                            $record->saveQuietly();

                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body('Nomor WhatsApp tidak tersedia.')
                                ->send();

                            return;
                        }

                        $token = WhatsappSetting::first()?->fonnte_token;

                        if (!$token) {
                            Log::error('Token Fonnte tidak tersedia di WhatsappSetting.');
                            $record->whatsapp_notified = false;
                            $record->saveQuietly();

                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body('Token Fonnte belum disetel.')
                                ->send();

                            return;
                        }

                        $dateFormatted  = Carbon::parse($record->start_date)->translatedFormat('l, d F Y');
                        $totalFormatted = 'Rp. ' . number_format($record->total_price, 0, ',', '.');
                        $customerName   = $customer->user->name;
                        $isTerkendala   = $record->status === 'Terkendala';

                        $lines = [
                            "~~ Sinar Laundry ~~",
                            "",
                            "*Pesanan Anda:*",
                            "Pada                : {$dateFormatted}",
                            "Atas Nama      : {$customerName}",
                            "Paket Pesanan : {$record->order_package}",
                        ];

                        if (!$isTerkendala) {
                            $lines[] = "Biaya Pesanan : *{$totalFormatted}*";
                        }

                        if ($isTerkendala) {
                            $lines[] = "*Sedang Terkendala ⚠️*";
                            $lines[] = "";
                            $lines[] = "Penyebab kendala: {$record->laundry_note}";
                            $lines[] = "";
                            $lines[] = "Mohon maaf atas ketidaknyamanannya.";
                        } else {
                            $lines[] = "*Telah Selesai ✅*";
                            $lines[] = "";
                            $lines[] = "Anda dapat mengajukan *pengantaran* atau mengambilnya secara langsung.";
                        }

                        $message = implode("\n", $lines);
                        $target = $customer->whatsapp;

                        try {
                            $curl = curl_init();
                            curl_setopt_array($curl, [
                                CURLOPT_URL => 'https://api.fonnte.com/send',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_POSTFIELDS => [
                                    'target'  => $target,
                                    'message' => $message,
                                ],
                                CURLOPT_HTTPHEADER => [
                                    "Authorization: {$token}",
                                ],
                            ]);

                            $response = curl_exec($curl);
                            $error = curl_error($curl);
                            curl_close($curl);

                            if ($error) {
                                Log::error("cURL error saat kirim ke Fonnte: {$error}");
                                $record->whatsapp_notified = false;

                                Notification::make()
                                    ->danger()
                                    ->title('Gagal')
                                    ->body('Gagal mengirim pesan WhatsApp.')
                                    ->send();
                            } else {
                                $responseData = json_decode($response, true);

                                if (isset($responseData['status']) && $responseData['status'] == true) {
                                    $record->whatsapp_notified = true;

                                    Notification::make()
                                        ->success()
                                        ->title('Berhasil')
                                        ->body('Pesan WhatsApp berhasil dikirim.')
                                        ->send();
                                } else {
                                    Log::warning("Fonnte gagal merespon sukses: {$response}");
                                    $record->whatsapp_notified = false;

                                    Notification::make()
                                        ->danger()
                                        ->title('Gagal')
                                        ->body('Fonnte gagal mengirim pesan.')
                                        ->send();
                                }

                                Log::info("Fonnte response untuk Order ID {$record->id}: {$response}");
                            }

                            $record->saveQuietly();
                        } catch (\Throwable $e) {
                            Log::error("Exception saat kirim ke Fonnte: " . $e->getMessage());
                            $record->whatsapp_notified = false;
                            $record->saveQuietly();

                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Terjadi kesalahan internal.')
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
                        TextEntry::make('customer.user.name')
                            ->label('Pelanggan')
                            ->color('info'),

                        TextEntry::make('entry_date')
                            ->label('Tanggal Masuk')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y H:i'))
                            ->extraAttributes(['class' => 'text-center']),

                        TextEntry::make('exit_date')
                            ->label('Tanggal Selesai')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('d-m-Y H:i'))
                            ->extraAttributes(['class' => 'text-center']),
                    ])
                    ->columns(3),


                Group::make([
                    InfolistSection::make('Pesanan')
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->columnSpanFull()
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'Baru' => 'info',
                                    'Selesai Diproses' => 'warning',
                                    'Selesai' => 'success',
                                    'Terkendala' => 'danger',
                                }),

                            TextEntry::make('laundry_note')
                                ->label('Penyebab Kendala')
                                ->prose()
                                ->alignJustify()
                                ->visible(fn($record) => $record->status === 'Terkendala'),

                            TextEntry::make('order_package')
                                ->label('Paket')
                                ->columnSpanFull(),

                            TextEntry::make('type')
                                ->label('Tipe')
                                ->columnSpanFull(),

                            TextEntry::make('price')
                                ->label('Tarif')
                                ->prefix('Rp. ')
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                                ->columnSpanFull(),
                        ])
                        ->extraAttributes(['class' => 'text-center'])
                        ->columns(1)
                        ->columnSpan(1),

                    InfolistSection::make('Detail')
                        ->schema([
                            TextEntry::make('weight')
                                ->label('Berat')
                                ->suffix(' kg')
                                ->visible(fn($record) => $record->type === 'Kiloan'),

                            TextEntry::make('length')
                                ->label('Ukuran')
                                ->visible(fn($record) => $record->type === 'Karpet')
                                ->formatStateUsing(function ($state, $record) {
                                    $length = $record->length / 100;
                                    $width = $record->width / 100;
                                    $area = $length * $width;

                                    return number_format($length, 2) . ' x ' . number_format($width, 2) . ' = ' . number_format($area, 2) . ' m²';
                                }),

                            TextEntry::make('quantity')
                                ->label('Jumlah')
                                ->suffix(' lembar')
                                ->visible(fn($record) => $record->type === 'Lembaran'),

                            TextEntry::make('quantity')
                                ->label('Jumlah')
                                ->suffix(' item')
                                ->visible(fn($record) => $record->type === 'Satuan'),

                            TextEntry::make('total_price_before_discount')
                                ->label('Total Tagihan')
                                ->visible(fn($record) => $record->discount_value > 0)
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                                ->prefix('Rp. '),

                            TextEntry::make('discount_value')
                                ->label(fn($record) => "Diskon {$record->discount_type}")
                                ->helperText(fn($record) => $record->discount_name)
                                ->visible(fn($record) => $record->discount_value > 0)
                                ->prefix(fn($record) => $record->discount_type === 'Langsung' ? 'Rp. ' : null)
                                ->suffix(fn($record) => $record->discount_type === 'Persentase' ? ' %' : null)
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),

                            TextEntry::make('total_price')
                                ->label(fn($record) => $record->discount_value > 0 ? 'Setelah Diskon' : 'Total Tagihan')
                                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                                ->prefix('Rp. ')
                                ->color('success'),
                        ])
                        ->extraAttributes(['class' => 'text-center'])
                        ->columns(1)
                        ->columnSpan(1),
                    InfolistSection::make('Bukti Gambar')
                        ->schema([
                            ImageEntry::make('retrieval_proof')
                                ->label('Pengambilan')
                                ->height(180),
                            ImageEntry::make('delivery_proof')
                                ->label('Pengantaran')
                                ->height(180),
                        ])
                        ->extraAttributes(['class' => 'text-center'])
                        ->columns(2),

                ])->columnSpanFull()->columns(2),
            ]);
    }
}
