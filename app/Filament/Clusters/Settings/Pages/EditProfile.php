<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage as BaseEditProfilePage;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class EditProfile extends BaseEditProfilePage
{
    use HasPageShield;

    protected static ?string $title = 'Profil Saya';

    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'settings/profile';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getSlug(): string
    {
        return static::$slug;
    }

    public static function getNavigationIcon(): ?string
    {
        return null;
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? 'Profil Saya';
    }
}
