<?php

namespace App\Filament\Resources\OrderPackageResource\Pages;

use App\Filament\Resources\OrderPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderPackages extends ListRecords
{
    protected static string $resource = OrderPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Paket Pesanan'),
        ];
    }
}
