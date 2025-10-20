<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerPickupDeliveryResource\Pages;
use App\Filament\Resources\CustomerPickupDeliveryResource\RelationManagers;
use App\Helper\ResourceCustomizing;
use App\Models\PickupDelivery as CustomerPickupDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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


class CustomerPickupDeliveryResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = CustomerPickupDelivery::class;

    protected static ?string $title = 'Riwayat Antar Jemput';

    protected static ?string $icon = 'heroicon-o-truck';

    protected static ?string $group = '';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('panel_user');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('20s')
            ->query(
                static::getEloquentQuery()
                    ->where('customer_id', auth()->user()->customer?->id ?? 0)
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Hari dan Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('l, j F Y')),

                Tables\Columns\TextColumn::make('time')
                    ->label('Pada jam')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->format('H:i')),

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

                Tables\Columns\IconColumn::make('whatsapp_notified_admin')
                    ->label('Notifikasi WA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->whatsapp_notified ? 'Terkirim' : 'Belum Terkirim'),

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
                Action::make('kirimWaAdmin')
                    ->label('Kirim Notifikasi WA')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        $record->status === 'Menunggu Konfirmasi' &&
                            !$record->whatsapp_notified_admin
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $token = WhatsappSetting::first()?->fonnte_token;
                        $adminNumber = WhatsappSetting::first()?->admin_whatsapp_number;

                        if (!$adminNumber || !$token) {
                            Log::error("WA Admin atau token tidak tersedia.");
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body('Nomor admin atau token Fonnte tidak tersedia.')
                                ->send();

                            return;
                        }

                        $customer = $record->customer;
                        $customerName = $customer->user->name;
                        $date = Carbon::parse($record->date)->locale('id')->translatedFormat('l, j F Y');
                        $time = Carbon::parse($record->time)->format('H:i');

                        $message = implode("\n", [
                            "~~ Sinar Laundry ~~",
                            "",
                            "*Ada permintaan antar jemput baru* dari pelanggan berikut",
                            "Nama                         : {$customerName}",
                            "Jenis Permintaan         : {$record->type}",
                            "Hari dan Tanggal       : {$date}",
                            "Pada Jam                    : {$time}",
                            "Catatan Pelanggan     : {$record->customer_note}",
                            "Alamat                        : {$customer->address}",
                            "",
                            "Silahkan lakukan konfirmasi lewat web."
                        ]);


                        try {
                            $curl = curl_init();
                            curl_setopt_array($curl, [
                                CURLOPT_URL => 'https://api.fonnte.com/send',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => [
                                    'target' => $adminNumber,
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
                                    ->body('Gagal mengirim pesan WA ke admin.')
                                    ->send();
                                return;
                            }

                            $data = json_decode($response, true);

                            if ($data['status'] ?? false) {
                                $record->whatsapp_notified_admin = true;
                                Notification::make()
                                    ->success()
                                    ->title('Berhasil')
                                    ->body('Pesan WA berhasil dikirim ke admin.')
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
                            Log::error("Exception saat kirim ke admin: {$e->getMessage()}");
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Terjadi kesalahan saat mengirim WA.')
                                ->send();
                        }
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Batal')
                    ->modalHeading('Batalkan Permintaan Antar Jemput')
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->visible(fn($record) => $record->status === 'Menunggu Konfirmasi'),
            ], position: ActionsPosition::BeforeColumns);;
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
            'index' => Pages\ListCustomerPickupDeliveries::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
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
                            ->label('Tipe'),
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
                            ->label('Catatan Pelanggan')
                            ->prose()
                            ->alignJustify(),
                    ])->inlineLabel(),
            ]);
    }
}
