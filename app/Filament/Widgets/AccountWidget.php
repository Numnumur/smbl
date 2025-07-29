<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseAccountWidget;

class AccountWidget extends BaseAccountWidget
{
    protected static ?int $sort = 1;

    public function getColumnSpan(): string
    {
        return 'full';
    }
}
