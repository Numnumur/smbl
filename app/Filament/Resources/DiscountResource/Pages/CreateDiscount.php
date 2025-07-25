<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use App\Helper\RedirectToListPage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    use RedirectToListPage;

    protected static string $resource = DiscountResource::class;
}
