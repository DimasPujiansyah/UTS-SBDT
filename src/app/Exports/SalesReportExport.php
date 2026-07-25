<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesReportExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected ?string $from = null,
        protected ?string $until = null,
    ) {}

    public function view(): View
    {
        $sales = Sale::with(['customer', 'items.product'])
            ->when($this->from, fn ($q) => $q->whereDate('sale_date', '>=', $this->from))
            ->when($this->until, fn ($q) => $q->whereDate('sale_date', '<=', $this->until))
            ->orderBy('sale_date')
            ->get();

        return view('exports.sales-report', [
            'sales' => $sales,
            'from' => $this->from,
            'until' => $this->until,
        ]);
    }
}
