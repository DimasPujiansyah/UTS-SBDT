<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $from = $request->query('from');
        $until = $request->query('until');

        $sales = Sale::with(['customer', 'items.product'])
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('sale_date', '<=', $until))
            ->orderBy('sale_date')
            ->get();

        $pdf = Pdf::loadView('exports.sales-report', compact('sales', 'from', 'until'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $from = $request->query('from');
        $until = $request->query('until');

        return Excel::download(
            new SalesReportExport($from, $until),
            'laporan-penjualan-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
