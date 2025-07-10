<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Helper\RedirectToListPage;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Belum Selesai' => Tab::make()
                ->modifyQueryUsing(
                    fn($query) => $query->where('status', '!=', 'Selesai')
                ),

            'Selesai' => Tab::make()
                ->modifyQueryUsing(
                    fn($query) =>
                    $query->where(
                        fn($query) => $query->where('status', 'Selesai')
                    )
                ),
        ];
    }
}
