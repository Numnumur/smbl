<?php

namespace App\Filament\Resources\OrderPackageResource\Pages;

use App\Filament\Resources\OrderPackageResource;
use App\Helper\RedirectToListPage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderPackage extends CreateRecord
{
    use RedirectToListPage;

    protected static string $resource = OrderPackageResource::class;
}
