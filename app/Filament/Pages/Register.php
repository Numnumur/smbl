<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\WhatsappSetting;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\SimplePage;
use Filament\Support\Enums\Alignment;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * @property Form $form
 */
class Register extends SimplePage
{
    public int $countdownSeconds = 0;

    public function decrementCountdown()
    {
        if ($this->countdownSeconds > 0) {
            $this->countdownSeconds--;
        }
    }


    protected static string $view = 'filament.pages.register';

    protected static string $layout = 'filament-panels::components.layout.base';

    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function register()
    {
        $data = $this->form->getState();

        $cachedCode = Cache::get("verification_code:{$data['whatsapp']}");

        if ($cachedCode != $data['verification_code']) {
            Notification::make()
                ->title('Kode verifikasi salah atau sudah kadaluarsa.')
                ->danger()
                ->send();
            return;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('panel_user');

        $user->customer()->create([
            'whatsapp' => $data['whatsapp'],
            'address' => $data['address'],
        ]);

        Filament::auth()->login($user);

        Cache::forget("verification_code:{$data['whatsapp']}");

        return app(RegistrationResponse::class);
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->unique(User::class, 'email')
                    ->validationMessages([
                        'email' => 'Format email tidak valid.',
                        'unique' => 'Email ini sudah terdaftar.',
                    ]),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->required()
                    ->password()
                    ->revealable(),
                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Kata Sandi')
                    ->password()
                    ->same('password')
                    ->revealable()
                    ->dehydrated(false)
                    ->validationMessages([
                        'same' => 'Konfirmasi kata sandi tidak cocok.',
                    ]),
                TextInput::make('whatsapp')
                    ->label('Nomor WhatsApp (WA)')
                    ->required()
                    ->prefix('+')
                    ->helperText('Contoh nomor WA: 628xxxxxxxxxx')
                    ->rules(['regex:/^62[0-9]{7,13}$/'])
                    ->validationMessages([
                        'regex' => 'Nomor WhatsApp harus diawali dengan 62 dan hanya boleh mengandung angka.',
                    ])
                    ->reactive(),
                TextInput::make('verification_code')
                    ->label('Kode Verifikasi WA')
                    ->required()
                    ->helperText('Masukkan kode yang dikirim ke WhatsApp Anda')
                    ->suffixAction(
                        Action::make('sendVerificationCode')
                            ->label('Kirim Kode')
                            ->icon('heroicon-o-paper-airplane')
                            ->color('success')
                            ->disabled(fn() => blank($this->data['whatsapp'] ?? null))
                            ->action('sendVerificationCode')
                    ),
                Textarea::make('address')
                    ->label('Alamat')
                    ->maxLength(300),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('register')
                ->label('Daftar')
                ->submit('register'),
        ];
    }

    public function getFormActionsAlignment(): string
    {
        return Alignment::Center->name;
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getCachedFormActions()
    {
        return $this->getFormActions();
    }

    public function sendVerificationCode()
    {
        $whatsapp = $this->data['whatsapp'] ?? null;

        if (!$whatsapp) {
            Notification::make()
                ->danger()
                ->title('Nomor WhatsApp tidak boleh kosong.')
                ->send();
            return;
        }

        try {
            $code = random_int(100000, 999999);
            Cache::put("verification_code:{$whatsapp}", $code, now()->addMinutes(1));
            $this->countdownSeconds = 60;

            $message = "*Kode Verifikasi* Anda adalah: *{$code}*\n\nKode ini berlaku selama 1 menit.";
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query([
                    'target' => $whatsapp,
                    'message' => $message,
                ]),
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . WhatsappSetting::first()?->fonnte_token,
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new \Exception("cURL Error: $err");
            }

            Notification::make()
                ->success()
                ->title('Kode verifikasi telah dikirim.')
                ->send();
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim kode verifikasi WA: ' . $e->getMessage());

            Notification::make()
                ->danger()
                ->title('Gagal mengirim kode verifikasi.')
                ->body('Silakan coba beberapa saat lagi.')
                ->send();
        }
    }
}
