<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; }
        h2 { margin-bottom: 0; }
        .subtitle { color: #64748b; margin-top: 2px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #2563eb; color: #fff; }
        tfoot td { font-weight: bold; background: #f1f5f9; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Sistem Penjualan Minimarket Terdistribusi</h2>
    <p class="subtitle">
        Laporan Penjualan
        @if($from || $until)
            ({{ $from ?? '...' }} s/d {{ $until ?? '...' }})
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>No. Invoice</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Jumlah Item</th>
                <th class="text-right">Total (Rp)</th>
                <th>Metode Bayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->sale_date->format('d-m-Y H:i') }}</td>
                    <td>{{ $sale->customer->name ?? 'Umum' }}</td>
                    <td>{{ $sale->items->count() }}</td>
                    <td class="text-right">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    <td>{{ strtoupper($sale->payment_method) }}</td>
                    <td>{{ ucfirst($sale->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada data transaksi pada rentang ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total</td>
                <td class="text-right">{{ number_format($sales->sum('total_amount'), 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
