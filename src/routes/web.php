<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Illuminate\Support\Facades\Response;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/
Route::get('/', function () {
    return redirect('/admin');
});

// Cetak Invoice / Struk Penjualan (dipakai oleh tombol "Cetak" pada halaman Transaksi)
Route::get('/laporan/penjualan/pdf', [\App\Http\Controllers\SalesReportController::class, 'exportPdf'])
    ->name('laporan.penjualan.pdf')
    ->middleware('auth');

Route::get('/laporan/penjualan/excel', [\App\Http\Controllers\SalesReportController::class, 'exportExcel'])
    ->name('laporan.penjualan.excel')
    ->middleware('auth');
