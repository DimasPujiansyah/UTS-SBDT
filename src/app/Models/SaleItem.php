<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = ['sale_id', 'product_id', 'quantity', 'price', 'subtotal'];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::saving(function (SaleItem $item) {
            $item->subtotal = $item->quantity * $item->price;
        });

        // Kurangi stok produk setiap kali item transaksi disimpan
        static::created(function (SaleItem $item) {
            $item->product()->decrement('stock', $item->quantity);
            static::recalculateSaleTotal($item->sale_id);
        });

        static::updated(function (SaleItem $item) {
            static::recalculateSaleTotal($item->sale_id);
        });

        // Kembalikan stok jika item dihapus (misal transaksi dibatalkan)
        static::deleted(function (SaleItem $item) {
            $item->product()->increment('stock', $item->quantity);
            static::recalculateSaleTotal($item->sale_id);
        });
    }

    protected static function recalculateSaleTotal(int $saleId): void
    {
        $total = static::where('sale_id', $saleId)->sum('subtotal');
        Sale::whereKey($saleId)->update(['total_amount' => $total]);
    }
}
