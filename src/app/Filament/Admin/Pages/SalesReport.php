<?php

namespace App\Filament\Admin\Pages;

use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;

class SalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $title = 'Laporan Penjualan';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.admin.pages.sales-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'from' => now()->startOfMonth()->toDateString(),
            'until' => now()->toDateString(),
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                ]),
            ])
            ->statePath('data');
    }

    #[Computed]
    public function sales()
    {
        $data = $this->form->getState();

        return Sale::with(['customer', 'items'])
            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('sale_date', '>=', $date))
            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('sale_date', '<=', $date))
            ->orderByDesc('sale_date')
            ->limit(200)
            ->get();
    }

    public function getExportPdfUrl(): string
    {
        $data = $this->form->getState();

        return route('laporan.penjualan.pdf', [
            'from' => $data['from'] ?? null,
            'until' => $data['until'] ?? null,
        ]);
    }

    public function getExportExcelUrl(): string
    {
        $data = $this->form->getState();

        return route('laporan.penjualan.excel', [
            'from' => $data['from'] ?? null,
            'until' => $data['until'] ?? null,
        ]);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Laporan Penjualan';
    }
}
