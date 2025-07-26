<?php

namespace App\Filament\Clusters\Settings\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Locked;
use function Filament\authorize;
use App\Filament\Clusters\Settings;
use Filament\Forms\Components\Select;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Localization as LocalizationModel;

class Localization extends Page implements HasForms
{
    use InteractsWithForms, HasPageShield;

    public ?array $data = [];

    #[Locked]
    public ?LocalizationModel $record = null;

    protected static ?string $title = 'Lokalisasi';

    protected static string $view = 'filament.clusters.settings.pages.localization';

    protected static ?string $cluster = Settings::class;

    public function mount(): void
    {
        $this->record = LocalizationModel::first();

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
                $this->getGeneralSection(),
            ])
            ->statePath('data')
            ->operation('edit');
    }

    protected function getGeneralSection(): Component
    {
        return Section::make('Umum')
            ->schema([
                Select::make('timezone')
                    ->label('Zona Waktu')
                    ->options(
                        collect(\DateTimeZone::listIdentifiers())
                            ->mapWithKeys(function ($tz) {
                                $dateTimeZone = new \DateTimeZone($tz);
                                $offset = $dateTimeZone->getOffset(new \DateTime('now', $dateTimeZone));
                                $hours = intdiv($offset, 3600);
                                $minutes = ($offset % 3600) / 60;

                                $formattedOffset = 'GMT' . ($hours >= 0 ? '+' : '') . $hours . ':' . str_pad(abs($minutes), 2, '0', STR_PAD_LEFT);

                                return [$tz => "$tz ($formattedOffset)"];
                            })
                            ->toArray()
                    )
                    ->searchable()
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

        return redirect()->route('filament.admin.settings.pages.localization');
    }

    protected function handleRecordUpdate(LocalizationModel $record, array $data): LocalizationModel
    {
        $record->fill($data);
        $record->save();

        config(['app.timezone' => $data['timezone'] ?? 'UTC']);
        date_default_timezone_set($data['timezone'] ?? 'UTC');

        return $record;
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

    public static function canView(Model $record): bool
    {
        try {
            return authorize('update', $record)->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }
}
