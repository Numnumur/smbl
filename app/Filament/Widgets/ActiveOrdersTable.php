<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class ActiveOrdersTable extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '20s';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pesanan Aktif';

    protected function getTableQuery(): Builder
    {
        $customerId = auth()->user()?->customer?->id;

        return Order::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', ['Baru', 'Selesai Diproses'])
            ->orderByDesc('entry_date');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('order_package')
                ->label('Paket Pesanan'),

            Tables\Columns\TextColumn::make('entry_date')
                ->label('Tanggal')
                ->formatStateUsing(function ($state) {
                    if (!$state) return '-';

                    $date = \Carbon\Carbon::parse($state)->locale('id');
                    $formatted = $date->translatedFormat('j F Y');
                    $relative = $date->diffForHumans(null, false, false, 2);

                    return "$formatted ($relative)";
                }),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Baru' => 'info',
                    'Selesai Diproses' => 'warning',
                    'Selesai' => 'success',
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
