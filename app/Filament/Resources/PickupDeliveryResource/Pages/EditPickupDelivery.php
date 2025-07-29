<?php

namespace App\Filament\Resources\PickupDeliveryResource\Pages;

use App\Filament\Resources\PickupDeliveryResource;
use App\Helper\RedirectToListPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPickupDelivery extends EditRecord
{
    use RedirectToListPage;

    protected static string $resource = PickupDeliveryResource::class;
}
