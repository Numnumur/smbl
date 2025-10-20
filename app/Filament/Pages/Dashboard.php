<?php

namespace App\Filament\Pages;

use App\Models\PickupDelivery;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Services\Reports\FinanceIncomeReportService;
use App\Services\Reports\FinanceExpenseReportService;
use App\Services\Reports\RegularCustomerReportService;
use App\Services\Reports\CustomerReportService;
use App\Services\Reports\OrderWorkReportService;
use App\Services\Reports\DiscountReportService;
use App\Services\Reports\CustomerOrderEntryExitReportService;
use App\Services\Reports\PickupDeliveryReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Get;

class Dashboard extends BaseDashboard
{
    public function getHeaderActions(): array
    {
        $user = auth()->user();

        if ($user->hasRole('panel_user')) {
            $customer = $user->customer;

            $missingFields = collect([
                'Alamat' => $customer?->address,
                'Nomor WhatsApp' => $customer?->whatsapp,
            ])->filter(fn($value) => blank($value));

            $isIncomplete = $missingFields->isNotEmpty();

            return [
                Action::make('permintaan_antar_jemput')
                    ->label('Permintaan Antar Jemput')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->modalHeading('Buat Permintaan Antar Jemput')
                    ->modalWidth('lg')
                    ->form(function () use ($missingFields) {
                        $formComponents = [];

                        if ($missingFields->isNotEmpty()) {
                            $formComponents[] = Placeholder::make('data_belum_lengkap')
                                ->label('Data Anda Belum Lengkap')
                                ->content('Anda harus melengkapi data terlebih dahulu sebelum membuat permintaan antar jemput. Data yang belum ada: ' . $missingFields->keys()->join(', ') . '.')
                                ->extraAttributes(['class' => 'text-red-600 font-medium'])
                                ->columnSpanFull()
                                ->hintAction(
                                    FormAction::make('lengkapi')
                                        ->label('Lengkapi Sekarang')
                                        ->icon('heroicon-o-arrow-right')
                                        ->url(route('filament.admin.settings.pages.address-and-whatsapp'))
                                );
                        } else {
                            $formComponents[] = Section::make()
                                ->description('Wajib Diisi')
                                ->schema([
                                    Select::make('type')
                                        ->label('Jenis Permintaan')
                                        ->options([
                                            'Antar' => 'Antar',
                                            'Jemput' => 'Jemput',
                                            'Antar dan Jemput' => 'Antar dan Jemput',
                                        ])
                                        ->native(false)
                                        ->required()
                                        ->columnSpanFull(),
                                    DatePicker::make('date')
                                        ->label('Tanggal')
                                        ->required()
                                        ->minDate(now()->toDateString())
                                        ->rules(['after_or_equal:today'])
                                        ->validationMessages([
                                            'after_or_equal' => 'Tanggal harus hari ini atau setelahnya.',
                                        ])
                                        ->native(false)
                                        ->live(),

                                    TimePicker::make('time')
                                        ->label('Waktu')
                                        ->required()
                                        ->seconds(false)
                                        ->native(false)
                                        ->rules([
                                            function (Get $get) {
                                                return function (string $attribute, $value, $fail) use ($get) {
                                                    $selectedDate = $get('date');

                                                    if (!$selectedDate || !$value) {
                                                        return;
                                                    }

                                                    $dateOnly = Carbon::parse($selectedDate)->format('Y-m-d');
                                                    $timeOnly = Carbon::parse($value)->format('H:i:s');
                                                    $selectedDateTime = Carbon::parse($dateOnly . ' ' . $timeOnly);
                                                    $minimumDateTime = now()->addHour();

                                                    if ($selectedDateTime->lt($minimumDateTime)) {
                                                        $fail('Waktu paling cepat adalah 1 jam dari sekarang (' . $minimumDateTime->format('H:i') . ').');
                                                    }
                                                };
                                            }
                                        ]),
                                ])->columns();

                            $formComponents[] = Section::make()
                                ->description('Opsional (Boleh Dikosongkan)')
                                ->schema([
                                    Textarea::make('customer_note')
                                        ->label('Catatan')
                                        ->columnSpanFull()
                                        ->maxLength(300),
                                ])->columns();
                        }

                        return $formComponents;
                    })
                    ->modalSubmitAction($isIncomplete ? false : null)
                    ->modalSubmitActionLabel('Kirim')
                    ->modalCancelActionLabel('Tutup')
                    ->action(function (array $data) use ($user, $isIncomplete) {
                        if ($isIncomplete) {
                            return;
                        }

                        $customer = $user->customer;

                        PickupDelivery::create([
                            'type' => $data['type'],
                            'status' => 'Menunggu Konfirmasi',
                            'date' => $data['date'],
                            'time' => $data['time'],
                            'customer_note' => $data['customer_note'],
                            'customer_id' => $customer?->id,
                        ]);

                        $this->getSavedNotification()->send();
                    }),
            ];
        }

        if ($user->hasRole('super_admin')) {
            return [];
        }

        return [];
    }


    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title('Permintaan Berhasil Dibuat');
    }

    protected function getSavedNotification2(): Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan Berhasil Dibuat');
    }
}
