<?php

namespace App\Filament\Resources\DiscountResource\Pages;

use App\Filament\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListDiscounts extends ListRecords
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Berlaku Saat Ini' => Tab::make()
                ->modifyQueryUsing(
                    fn($query) =>
                    $query->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now())
                ),

            'Tidak Berlaku' => Tab::make()
                ->modifyQueryUsing(
                    fn($query) =>
                    $query->where(
                        fn($query) =>
                        $query->whereDate('end_date', '<', now())
                            ->orWhereDate('start_date', '>', now())
                    )
                ),
        ];
    }
}
