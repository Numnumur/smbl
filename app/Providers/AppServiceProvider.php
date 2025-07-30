<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\Localization;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Colors\Color;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\PickupDelivery;
use App\Observers\PickupDeliveryObserver;

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
        Order::observe(OrderObserver::class);
        PickupDelivery::observe(PickupDeliveryObserver::class);

        if (Schema::hasTable('localizations')) {

            $localization = Localization::first();

            $timezone = $localization?->timezone ?? 'UTC';
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }

        FilamentColor::register([
            'sky' => Color::Sky,
            'violet' => Color::Violet,
            'slate' => Color::Slate,
        ]);
    }
}
