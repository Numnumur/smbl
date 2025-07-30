<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use Filament\Pages\Page;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer as CustomerModel;
use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Exceptions\Halt;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\AuthorizationException;
use function Filament\authorize;
use Filament\Notifications\Notification;
use App\Helper\PageCustomizing;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;


class AddressAndWhatsapp extends Page
{
    use InteractsWithForms, PageCustomizing, HasPageShield;

    public ?array $data = [];

    #[Locked]
    public ?CustomerModel $record = null;

    protected static ?string $title = 'Alamat dan WhatsApp';

    protected static string $view = 'filament.clusters.settings.pages.address-and-whatsapp';

    protected static ?string $cluster = Settings::class;

    public function mount(): void
    {
        $this->record = Auth::user()?->customer;

        $this->fillForm();
    }

    public function fillForm(): void
    {
        $data = $this->record->attributesToArray();

        abort_unless(static::canView($this->record), 404);

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getCustomer(),
            ])
            ->statePath('data')
            ->operation('edit');
    }

    protected function getCustomer(): Component
    {
        return Section::make()
            ->schema([
                TextInput::make('whatsapp')
                    ->label('Nomor WhatsApp (WA)')
                    ->maxLength(15)
                    ->helperText('Contoh nomor WA: 628xxxxxxxxxx')
                    ->prefix('+')
                    ->rules([
                        'regex:/^62[0-9]{7,13}$/',
                    ])
                    ->validationMessages([
                        'regex' => 'Nomor WhatsApp harus diawali dengan 62 dan hanya boleh mengandung angka tanpa spasi atau karakter lain.',
                    ])
                    ->inputMode('numeric')
                    ->extraAttributes([
                        'inputmode' => 'numeric',
                        'pattern' => '[0-9]*',
                    ]),
                Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull()
                    ->maxLength(300),
            ])->columns();
    }

    public function save()
    {
        try {
            $data = $this->form->getState();

            $this->handleRecordUpdate($this->record, $data);
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()->send();

        return redirect()->route('filament.admin.settings.pages.address-and-whatsapp');
    }

    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'));
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function handleRecordUpdate(CustomerModel $record, array $data): CustomerModel
    {
        $record->fill($data);

        $record->save();

        return $record;
    }

    public static function canView(Model $record): bool
    {
        try {
            return authorize('update', $record)->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }
}
