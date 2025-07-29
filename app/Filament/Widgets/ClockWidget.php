<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ClockWidget extends Widget
{
    protected static ?int $sort = 99;

    protected static string $view = 'filament.widgets.clock-widget';

    protected static ?string $pollingInterval = '20s';

    public function getColumnSpan(): string
    {
        return 'full';
    }
}
