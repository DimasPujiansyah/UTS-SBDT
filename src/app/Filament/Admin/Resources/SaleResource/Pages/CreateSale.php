<?php

namespace App\Filament\Admin\Resources\SaleResource\Pages;

use App\Filament\Admin\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function afterCreate(): void
    {
        // total_amount sudah dihitung ulang otomatis oleh SaleItem observer,
        // di sini kita hitung kembalian setelah semua item tersimpan.
        $this->record->refresh();
        $this->record->update([
            'change_amount' => max(0, $this->record->paid_amount - $this->record->total_amount),
        ]);
    }
}
