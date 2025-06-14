<?php

namespace App\Filament\Resources\OrderPackageResource\Pages;

use App\Filament\Resources\OrderPackageResource;
use App\Helper\RedirectToListPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderPackage extends EditRecord
{
    use RedirectToListPage;

    protected static string $resource = OrderPackageResource::class;
}
