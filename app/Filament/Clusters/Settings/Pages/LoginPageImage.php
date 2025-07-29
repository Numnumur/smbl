<?php

namespace App\Filament\Clusters\Settings\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Locked;
use App\Filament\Clusters\Settings;
use App\Models\LoginPageImage as ImageModel;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use App\Helper\PageCustomizing;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use function Filament\authorize;

class LoginPageImage extends Page
{
    use InteractsWithForms, PageCustomizing, HasPageShield;

    public ?array $data = [];

    #[Locked]
    public ?ImageModel $record = null;

    protected static ?string $title = 'Gambar Halaman Login';

    protected static string $view = 'filament.clusters.settings.pages.login-page-image';

    protected static ?string $cluster = Settings::class;

    public function mount(): void
    {
        $this->record = ImageModel::firstOrNew();

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
                $this->getLogoSection(),
                $this->getLoginBackgroundSection(),
            ])
            ->statePath('data')
            ->operation('edit');
    }

    protected function getLogoSection(): Component
    {
        return Section::make()
            ->schema([
                FileUpload::make('logo')
                    ->label('Logo')
                    ->openable()
                    ->maxSize(2048)
                    ->visibility('public')
                    ->disk('public')
                    ->directory('login-page-image')
                    ->imageResizeMode('contain')
                    ->removeUploadedFileButtonPosition('center bottom')
                    ->uploadButtonPosition('center bottom')
                    ->uploadProgressIndicatorPosition('center bottom')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg', 'image/webp'])
                    ->extraAttributes(['class' => 'w-48 h-auto'])
                    ->getUploadedFileNameForStorageUsing(fn($file) => 'logo.' . $file->getClientOriginalExtension()),
            ])->columns();
    }

    protected function getLoginBackgroundSection(): Component
    {
        return Section::make()
            ->schema([
                FileUpload::make('login_background')
                    ->label('Latar Belakang')
                    ->openable()
                    ->maxSize(4096)
                    ->visibility('public')
                    ->disk('public')
                    ->directory('login-page-image')
                    ->imageResizeMode('contain')
                    ->removeUploadedFileButtonPosition('center bottom')
                    ->uploadButtonPosition('center bottom')
                    ->uploadProgressIndicatorPosition('center bottom')
                    ->extraAttributes(['class' => 'w-full h-64'])
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg', 'image/webp'])
                    ->getUploadedFileNameForStorageUsing(fn($file) => 'login-bg.' . $file->getClientOriginalExtension()),
            ])->columns();
    }

    public function save()
    {
        try {
            $data = $this->form->getState();

            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                $data['logo']->storeAs('login-page-image', 'logo.' . $data['logo']->getClientOriginalExtension(), 'public');
            }

            if (isset($data['login_background']) && $data['login_background'] instanceof \Illuminate\Http\UploadedFile) {
                $data['login_background']->storeAs('login-page-image', 'login-bg.' . $data['login_background']->getClientOriginalExtension(), 'public');
            }

            $this->handleRecordUpdate($this->record, $data);
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()->send();

        return redirect()->route('filament.admin.settings.pages.login-page-image');
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

    protected function handleRecordUpdate(ImageModel $record, array $data): ImageModel
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
