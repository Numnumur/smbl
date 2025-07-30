<?php

namespace App\Filament\Resources\WhatsappBroadcastResource\Pages;

use App\Filament\Resources\WhatsappBroadcastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWhatsappBroadcasts extends ListRecords
{
    protected static string $resource = WhatsappBroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
