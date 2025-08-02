<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappBroadcastResource\Pages;
use App\Filament\Resources\WhatsappBroadcastResource\RelationManagers;
use App\Models\WhatsappBroadcast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Helper\ResourceCustomizing;
use Filament\Forms\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\WhatsappSetting;
use Illuminate\Support\Carbon;

class WhatsappBroadcastResource extends Resource
{
    use ResourceCustomizing;

    protected static ?string $model = WhatsappBroadcast::class;

    protected static ?string $title = 'Siaran Pesan WhatsApp';

    protected static ?string $icon = 'heroicon-o-megaphone';

    protected static ?string $group = 'Lainnya';

    protected static ?int $navigationSort = 42;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nama Siaran')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('message_content')
                            ->label('Isi Pesan')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Hanya bagian ini yang akan dikirimkan ke seluruh nomor WhatsApp pelanggan yang ada.'),
                    ])->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Siaran')
                    ->searchable(),
                Tables\Columns\IconColumn::make('whatsapp_notified')
                    ->label('Notifikasi WA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->whatsapp_notified ? 'Terkirim' : 'Belum Terkirim'),
                Tables\Columns\TextColumn::make('recipient_count')
                    ->label('Jumlah Penerima Pesan')
                    ->numeric(),
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
            ->actions([
                Action::make('kirimPesanWa')
                    ->label('Kirim Pesan WA')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn($record) => !$record->whatsapp_notified)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $token = WhatsappSetting::first()?->fonnte_token;

                        if (!$token) {
                            Log::error("Token Fonnte tidak tersedia.");
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body('Token Fonnte tidak tersedia.')
                                ->send();
                            return;
                        }

                        $numbers = Customer::whereNotNull('whatsapp')
                            ->where('whatsapp', '!=', '')
                            ->pluck('whatsapp')
                            ->unique()
                            ->values()
                            ->toArray();

                        if (count($numbers) === 0) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body('Tidak ada nomor WhatsApp pelanggan yang tersedia.')
                                ->send();
                            return;
                        }

                        $target = implode(',', $numbers);
                        $message = $record->message_content;

                        try {
                            $curl = curl_init();
                            curl_setopt_array($curl, [
                                CURLOPT_URL => 'https://api.fonnte.com/send',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => [
                                    'target' => $target,
                                    'message' => $message,
                                    'delay' => 2,
                                ],
                                CURLOPT_HTTPHEADER => [
                                    "Authorization: {$token}",
                                ],
                            ]);

                            $response = curl_exec($curl);
                            $error = curl_error($curl);
                            curl_close($curl);

                            if ($error) {
                                Log::error("cURL error broadcast WA: {$error}");
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal')
                                    ->body('Gagal mengirim pesan broadcast WA.')
                                    ->send();
                                return;
                            }

                            $data = json_decode($response, true);
                            if ($data['status'] ?? false) {
                                $record->update([
                                    'whatsapp_notified' => true,
                                    'recipient_count' => count($numbers),
                                    'send_date' => now(),
                                ]);

                                Notification::make()
                                    ->success()
                                    ->title('Berhasil')
                                    ->body('Pesan broadcast berhasil dikirim ke seluruh pelanggan.')
                                    ->send();
                            } else {
                                Log::warning("Fonnte gagal broadcast: {$response}");
                                Notification::make()
                                    ->danger()
                                    ->title('Gagal')
                                    ->body('Pengiriman pesan gagal oleh API Fonnte.')
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Log::error("Exception saat broadcast WA: {$e->getMessage()}");
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Terjadi kesalahan saat mengirim broadcast WA.')
                                ->send();
                        }
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWhatsappBroadcasts::route('/'),
            'create' => Pages\CreateWhatsappBroadcast::route('/create'),
            'edit' => Pages\EditWhatsappBroadcast::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make()
                    ->schema([
                        TextEntry::make('title')
                            ->label('Nama Siaran'),
                        TextEntry::make('message_content')
                            ->label('Isi Pesan')
                            ->prose()
                            ->alignJustify(),
                        TextEntry::make('send_date')
                            ->label('Waktu Pengiriman Pesan')
                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('j F Y H:i'))
                            ->extraAttributes(['class' => 'text-center']),
                        TextEntry::make('recipient_count')
                            ->label('Jumlah Penerima Pesan'),
                    ])->inlineLabel(),
            ]);
    }
}
