<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PickupDeliveryResource\Pages;
use App\Filament\Resources\PickupDeliveryResource\RelationManagers;
use App\Models\PickupDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Helper\ResourceCustomizing;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use App\Models\WhatsappSetting;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


class PickupDeliveryResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = PickupDelivery::class;

    protected static ?string $title = 'Antar Jemput';

    protected static ?string $icon = 'heroicon-o-truck';

    protected static ?string $group = '';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Placeholder::make('name')
                            ->label('Pelanggan')
                            ->content(fn($record): string => $record?->customer?->user?->name ?? '-'),

                        Placeholder::make('tanggal')
                            ->label('Hari dan Tanggal')
                            ->content(
                                fn($record): string =>
                                $record?->date
                                    ? \Carbon\Carbon::parse($record->date)->locale('id')->translatedFormat('l, j F Y')
                                    : '-'
                            ),

                        Placeholder::make('waktu')
                            ->label('Pada Jam')
                            ->content(
                                fn($record): string =>
                                $record?->time
                                    ? \Carbon\Carbon::parse($record->time)->translatedFormat('H:i')
                                    : '-'
                            ),

                        Textarea::make('estimation_note')
                            ->label('Catatan Estimasi')
                            ->columnSpanFull()
                            ->maxLength(300)
                            ->visible(fn(Forms\Get $get) => in_array($get('status'), ['Sudah Dikonfirmasi', 'Selesai']))
                            ->disabled(fn(Forms\Get $get) => $get('status') === 'Selesai')
                            ->required(fn(Forms\Get $get) => $get('status') === 'Sudah Dikonfirmasi'),

                        Placeholder::make('type')
                            ->label('Jenis Permintaan')
                            ->content(fn($record): string => $record?->type ?? '-'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Menunggu Konfirmasi' => 'Menunggu Konfirmasi',
                                'Sudah Dikonfirmasi' => 'Sudah Dikonfirmasi',
                                'Selesai' => 'Selesai',
                                'Ditolak' => 'Ditolak',
                            ])
                            ->native(false)
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),

                        Textarea::make('laundry_note')
                            ->label('Alasan Penolakan')
                            ->columnSpanFull()
                            ->maxLength(300)
                            ->visible(fn(Forms\Get $get) => $get('status') === 'Ditolak')
                            ->required(fn(Forms\Get $get) => $get('status') === 'Ditolak'),

                        Placeholder::make('customer_note')
                            ->label('Catatan Dari Pelanggan')
                            ->content(fn($record): string => $record?->customer_note ?? '-'),
                    ])->inlineLabel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('20s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.user.name')
                    ->label('Pelanggan'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Hari dan Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('l, j F Y')),

                Tables\Columns\TextColumn::make('time')
                    ->label('Pada Jam')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('H:i')),

                Tables\Columns\TextColumn::make('estimation_note')
                    ->label('Catatan Estimasi')
                    ->placeholder('-')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis Permintaan'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Menunggu Konfirmasi' => 'info',
                        'Sudah Dikonfirmasi' => 'warning',
                        'Selesai' => 'success',
                        'Ditolak' => 'danger',
                    }),

                Tables\Columns\IconColumn::make('whatsapp_notified_customer')
                    ->label('Notifikasi WA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->whatsapp_notified_customer ? 'Terkirim' : 'Belum Terkirim'),

                Tables\Columns\TextColumn::make('customer_note')
                    ->label('Catatan Pelanggan')
                    ->placeholder('-')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('laundry_note')
                    ->label('Alasan Penolakan')
                    ->placeholder('-')
                    ->limit(50)
                    ->wrap(),

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
                SelectFilter::make('type')
                    ->label('Jenis Permintaan')
                    ->options([
                        'Antar' => 'Antar',
                        'Jemput' => 'Jemput',
                        'Antar dan Jemput' => 'Antar dan Jemput',
                    ])
                    ->native(false),
            ])
            ->actions([
                Action::make('kirimWaCustomer')
                    ->label('Kirim Notifikasi WA')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        !$record->whatsapp_notified_customer &&
                            in_array($record->status, ['Sudah Dikonfirmasi', 'Ditolak'])
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $token = WhatsappSetting::first()?->fonnte_token;

                        $customer = $record->customer;
                        $target = $customer->whatsapp;

                        if (!$token || !$target) {
                            Log::error("Token Fonnte atau nomor customer tidak tersedia.");
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body('Token atau nomor WA customer belum disetel.')
                                ->send();
                            return;
                        }

                        $customerName = $customer->user->name;
                        $date = Carbon::parse($record->date)->locale('id')->translatedFormat('l, j F Y');
                        $time = Carbon::parse($record->time)->format('H:i');

                        $header = [
                            "~~ Sinar Laundry ~~",
                            "",
                            "*Permintaan Antar Jemput Anda*",
                            "Atas Nama                  : {$customerName}",
                            "Jenis Permintaan         : {$record->type}",
                            "Hari dan Tanggal       : {$date}",
                            "Pada Jam                    : {$time}",
                            "Catatan Pelanggan     : {$record->customer_note}",
                            "",
                        ];

                        if ($record->status === 'Sudah Dikonfirmasi') {
                            $body = [
                                "✅ *Sudah dikonfirmasi oleh admin* dan akan dilakukan sesuai dengan waktu yang anda tentukan.",
                                "",
                                "Catatan Estimasi:",
                                $record->estimation_note,
                            ];
                        } elseif ($record->status === 'Ditolak') {
                            $body = [
                                "❌ *Ditolak oleh admin*.",
                                "Penyebab Penolakan: {$record->laundry_note}",
                                "",
                                "Dimohon pengertiannya, anda juga dapat mengajukan permintaan antar jemput lagi.",
                            ];
                        } else {
                            return;
                        }

                        $message = implode("\n", array_merge($header, $body));

                        try {
                            $curl = curl_init();
                            curl_setopt_array($curl, [
                                CURLOPT_URL => 'https://api.fonnte.com/send',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => [
                                    'target' => $target,
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
                                Log::error("cURL error: {$error}");
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal')
                                    ->body('Gagal mengirim WA ke customer.')
                                    ->send();
                                return;
                            }

                            $data = json_decode($response, true);

                            if ($data['status'] ?? false) {
                                $record->whatsapp_notified_customer = true;
                                Notification::make()
                                    ->success()
                                    ->title('Berhasil')
                                    ->body('Pesan WA berhasil dikirim ke customer.')
                                    ->send();
                            } else {
                                Log::warning("Fonnte gagal: {$response}");
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal')
                                    ->body('Fonnte gagal merespon sukses.')
                                    ->send();
                            }

                            $record->saveQuietly();
                        } catch (\Throwable $e) {
                            Log::error("Exception saat kirim WA ke customer: {$e->getMessage()}");
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Terjadi kesalahan saat mengirim WA.')
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPickupDeliveries::route('/'),
            'edit' => Pages\EditPickupDelivery::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('customer.user.name')
                            ->label('Pelanggan'),
                        TextEntry::make('date')
                            ->label('Hari dan Tanggal')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('l , j F Y'))
                            ->extraAttributes(['class' => 'text-center']),
                        TextEntry::make('time')
                            ->label('Pada Jam')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('H:i'))
                            ->extraAttributes(['class' => 'text-center']),
                        TextEntry::make('estimation_note')
                            ->label('Catatan Estimasi')
                            ->prose()
                            ->alignJustify()
                            ->visible(fn($record) => in_array($record->status, ['Sudah Dikonfirmasi', 'Selesai'])),
                        TextEntry::make('type')
                            ->label('Jenis Permintaan'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->colors([
                                'info' => static fn($state): bool => $state === 'Menunggu Konfirmasi',
                                'warning' => static fn($state): bool => $state === 'Sudah Dikonfirmasi',
                                'success' => static fn($state): bool => $state === 'Selesai',
                                'danger' => static fn($state): bool => $state === 'Ditolak',
                            ]),
                        TextEntry::make('laundry_note')
                            ->label('Alasan Penolakan')
                            ->prose()
                            ->alignJustify()
                            ->visible(fn($record) => $record->status === 'Ditolak'),
                        TextEntry::make('customer_note')
                            ->label('Catatan Dari Pelanggan')
                            ->prose()
                            ->alignJustify(),
                    ])->inlineLabel(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::whereNotIn('status', ['Selesai', 'Ditolak'])->count();
    }
}
