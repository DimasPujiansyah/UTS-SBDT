<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'sku',
        'name',
        'unit',
        'stock',
        'minimum_stock',
        'purchase_price',
        'selling_price',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /** Scope: barang dengan stok di bawah atau sama dengan ambang minimum */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'minimum_stock');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }

    protected static function booted(): void
    {
        // Kirim notifikasi database Filament ke semua admin saat stok menyentuh/di bawah minimum
        static::updated(function (Product $product) {
            if ($product->wasChanged('stock') && $product->stock <= $product->minimum_stock) {
                $admins = \App\Models\User::query()->get();

                Notification::make()
                    ->title('Stok Menipis')
                    ->body("Stok barang \"{$product->name}\" tersisa {$product->stock} {$product->unit} (minimum {$product->minimum_stock}).")
                    ->warning()
                    ->sendToDatabase($admins);
            }
        });
    }
}
