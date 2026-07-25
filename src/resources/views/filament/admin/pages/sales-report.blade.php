<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    <div class="flex gap-3">
        <x-filament::button tag="a" :href="$this->getExportPdfUrl()" target="_blank" icon="heroicon-o-document-arrow-down" color="danger">
            Export PDF
        </x-filament::button>

        <x-filament::button tag="a" :href="$this->getExportExcelUrl()" target="_blank" icon="heroicon-o-table-cells" color="success">
            Export Excel
        </x-filament::button>
    </div>

    <x-filament::section heading="Hasil">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-2">No. Invoice</th>
                    <th class="py-2">Tanggal</th>
                    <th class="py-2">Pelanggan</th>
                    <th class="py-2">Item</th>
                    <th class="py-2 text-right">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->sales as $sale)
                    <tr class="border-b">
                        <td class="py-2">{{ $sale->invoice_number }}</td>
                        <td class="py-2">{{ $sale->sale_date->format('d-m-Y H:i') }}</td>
                        <td class="py-2">{{ $sale->customer->name ?? 'Umum' }}</td>
                        <td class="py-2">{{ $sale->items->count() }}</td>
                        <td class="py-2 text-right">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">Tidak ada data pada rentang tanggal ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>
