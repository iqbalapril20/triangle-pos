<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\Sale;

class ProductReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $start_date;
    public $end_date;

    protected $rules = [
        'start_date' => 'required|date|before_or_equal:end_date',
        'end_date'   => 'required|date|after_or_equal:start_date',
    ];

    public function mount()
    {
        $this->start_date = today()->subDays(30)->format('Y-m-d');
        $this->end_date = today()->format('Y-m-d');
    }

    public function generateReport()
    {
        $this->validate();
        $this->resetPage();
    }

    public function render()
    {
        // Get all completed sales IDs within the date range
        $completedSaleIds = Sale::completed()
            ->whereDate('date', '>=', $this->start_date)
            ->whereDate('date', '<=', $this->end_date)
            ->pluck('id');

        // Fetch sale details matching these sale IDs, group by product, and sum quantities & sub_totals
        $products = SaleDetails::selectRaw('
                product_id, 
                product_name, 
                product_code, 
                SUM(quantity) as total_quantity, 
                SUM(sub_total) as total_revenue
            ')
            ->whereIn('sale_id', $completedSaleIds)
            ->groupBy('product_id', 'product_name', 'product_code')
            ->orderBy('total_quantity', 'desc')
            ->paginate(10);

        return view('livewire.reports.product-report', [
            'products' => $products
        ]);
    }
}
