<?php

namespace App\Filament\Resources\PickupDeliveryResource\Pages;

use App\Filament\Resources\PickupDeliveryResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListPickupDeliveries extends ListRecords
{
    protected static string $resource = PickupDeliveryResource::class;

    public function getTabs(): array
    {
        return [
            Tab::make('Aktif')
                ->modifyQueryUsing(
                    fn($query) => $query
                        ->where('status', '!=', 'Selesai')
                        ->where('status', '!=', 'Ditolak'),
                ),

            Tab::make('Selesai')
                ->modifyQueryUsing(
                    fn($query) => $query
                        ->where('status', 'Selesai'),
                ),

            Tab::make('Ditolak')
                ->modifyQueryUsing(
                    fn($query) => $query
                        ->where('status', 'Ditolak'),
                ),
        ];
    }
}
