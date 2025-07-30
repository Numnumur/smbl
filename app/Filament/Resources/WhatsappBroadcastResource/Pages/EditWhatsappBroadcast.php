<?php

namespace App\Filament\Resources\WhatsappBroadcastResource\Pages;

use App\Filament\Resources\WhatsappBroadcastResource;
use App\Helper\RedirectToListPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsappBroadcast extends EditRecord
{
    use RedirectToListPage;

    protected static string $resource = WhatsappBroadcastResource::class;
}
