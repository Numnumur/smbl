<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\Localization;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('localizations')) {
            $localization = Localization::first();

            $timezone = $localization?->timezone ?? 'UTC';
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }
    }
}
