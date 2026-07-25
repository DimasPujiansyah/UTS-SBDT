<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan (14 Hari Terakhir)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn ($i) => today()->subDays($i));

        $totals = $days->map(function ($day) {
            return Sale::whereDate('sale_date', $day)
                ->where('status', 'completed')
                ->sum('total_amount');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Rp)',
                    'data' => $totals->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn ($d) => $d->translatedFormat('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
