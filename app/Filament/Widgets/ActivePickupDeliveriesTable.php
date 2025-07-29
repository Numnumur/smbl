<?php

namespace App\Filament\Widgets;

use App\Models\PickupDelivery;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class ActivePickupDeliveriesTable extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = '20s';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Antar Jemput Aktif';

    protected function getTableQuery(): Builder
    {
        $customerId = auth()->user()?->customer?->id;

        return PickupDelivery::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', ['Menunggu Konfirmasi', 'Sudah Dikonfirmasi'])
            ->orderByDesc('date_and_time');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('type')
                ->label('Tipe'),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Menunggu Konfirmasi' => 'info',
                    'Sudah Dikonfirmasi' => 'warning',
                    'Selesai' => 'success',
                    'Ditolak' => 'danger',
                    default => 'gray',
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->paginated(false);
    }
}
