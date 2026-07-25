<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayRevenue = Sale::whereDate('sale_date', today())
            ->where('status', 'completed')
            ->sum('total_amount');

        $monthRevenue = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $todayTransactions = Sale::whereDate('sale_date', today())->count();

        $lowStockCount = Product::lowStock()->count();

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description($todayTransactions . ' transaksi hari ini')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Penjualan Bulan Ini', 'Rp ' . number_format($monthRevenue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Total Barang', Product::count())
                ->description($lowStockCount . ' barang stok menipis')
                ->descriptionIcon('heroicon-m-cube')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Total Pelanggan', Customer::count())
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
