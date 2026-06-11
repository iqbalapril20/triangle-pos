<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Sale\Entities\Sale;

class SalesReport extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $customers;
    public $start_date;
    public $end_date;
    public $customer_id;
    public $sale_status;
    public $payment_status;

    protected $rules = [
        'start_date' => 'required|date|before:end_date',
        'end_date'   => 'required|date|after:start_date',
    ];

    public function mount($customers)
    {
        $this->customers = $customers;
        $this->start_date = today()->subDays(30)->format('Y-m-d');
        $this->end_date = today()->format('Y-m-d');
        $this->customer_id = '';
        $this->sale_status = '';
        $this->payment_status = '';
    }

    public function render()
    {
        $query = Sale::whereDate('date', '>=', $this->start_date)
            ->whereDate('date', '<=', $this->end_date)
            ->when($this->customer_id, function ($query) {
                return $query->where('customer_id', $this->customer_id);
            })
            ->when($this->sale_status, function ($query) {
                return $query->where('status', $this->sale_status);
            })
            ->when($this->payment_status, function ($query) {
                return $query->where('payment_status', $this->payment_status);
            });

        // Hitung Grand Total sebelum Paginate (Harus dibagi 100 karena Accessor model tidak dipanggil kalau pakai sum())
        $totalAmount = (clone $query)->sum('total_amount') / 100;
        $paidAmount = (clone $query)->sum('paid_amount') / 100;
        $dueAmount = (clone $query)->sum('due_amount') / 100;

        $sales = (clone $query)->orderBy('date', 'desc')->paginate(10);

        return view('livewire.reports.sales-report', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'dueAmount' => $dueAmount,
        ]);
    }

    public function generateReport()
    {
        $this->validate();
        $this->render();
    }
}
