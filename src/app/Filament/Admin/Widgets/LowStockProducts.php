<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProducts extends BaseWidget
{
    protected static ?string $heading = 'Barang dengan Stok Menipis';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->lowStock()->orderBy('stock'))
            ->columns([
                Tables\Columns\TextColumn::make('sku')->label('SKU'),
                Tables\Columns\TextColumn::make('name')->label('Nama Barang'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color('danger')
                    ->suffix(fn (Product $record) => ' ' . $record->unit),
                Tables\Columns\TextColumn::make('minimum_stock')->label('Stok Minimum'),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->default('-'),
            ])
            ->paginated([5, 10, 25]);
    }
}
