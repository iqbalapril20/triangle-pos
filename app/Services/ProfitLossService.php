<?php

namespace App\Services;

use Modules\Expense\Entities\Expense;
use Modules\Purchase\Entities\Purchase;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\PurchasesReturn\Entities\PurchaseReturn;
use Modules\PurchasesReturn\Entities\PurchaseReturnPayment;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SalePayment;
use Modules\SalesReturn\Entities\SaleReturn;
use Modules\SalesReturn\Entities\SaleReturnPayment;

class ProfitLossService
{
    public static function summary($startDate, $endDate): array
    {
        // === PENJUALAN ===
        $totalSalesCount = Sale::completed()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->count();

        $salesAmount = Sale::completed()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('total_amount') / 100;

        // === RETURN PENJUALAN (untuk hitung revenue profit) ===
        $saleReturnsAmount = SaleReturn::completed()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('total_amount') / 100;

        // === PEMBELIAN ===
        $totalPurchasesCount = Purchase::completed()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->count();

        $purchasesAmount = Purchase::completed()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('total_amount') / 100;

        // === BIAYA OPERASIONAL ===
        $expensesAmount = Expense::query()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        // === KEUNTUNGAN (mengikuti Livewire calculateProfit) ===
        $revenue = $salesAmount - $saleReturnsAmount;

        $productCosts = 0;
        $sales = Sale::completed()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->with('saleDetails.product')
            ->get();

        foreach ($sales as $sale) {
            foreach ($sale->saleDetails as $detail) {
                // product_cost sudah otomatis /100 via accessor Product model
                $productCosts += (float) $detail->product->product_cost;
            }
        }

        $profitAmount = $revenue - $productCosts;

        // === UANG MASUK (mengikuti Livewire calculatePaymentsReceived) ===
        $salePayments = SalePayment::query()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        $purchaseReturnPayments = PurchaseReturnPayment::query()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        $uangMasuk = $salePayments + $purchaseReturnPayments;

        // === UANG KELUAR (mengikuti Livewire calculatePaymentsSent + change) ===
        $purchasePayments = PurchasePayment::query()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        $saleReturnPayments = SaleReturnPayment::query()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        // Kembalian = paid_amount - total_amount (kalau paid > total)
        $changeAmount = Sale::completed()
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->whereColumn('paid_amount', '>', 'total_amount')
            ->selectRaw('COALESCE(SUM(paid_amount - total_amount),0) as total_change')
            ->value('total_change') / 100;

        $uangKeluar = $purchasePayments + $saleReturnPayments + $expensesAmount;

        // ✅ SESUAI PERMINTAAN KAMU:
        $selisihKas = $uangMasuk - $uangKeluar;

        // Return: tetap sediakan key lama (biar PDF lama nggak pecah), + versi grouping
        return [
            // grouping biar gampang dipisah-pisah di PDF
            'penjualan' => [
                'trx_count'   => $totalSalesCount,
                'total_sales' => $salesAmount,
            ],
            'keuntungan' => [
                'profit' => $profitAmount,
            ],
            'pembelian' => [
                'trx_count' => $totalPurchasesCount,
                'total'     => $purchasesAmount,
            ],
            'biaya' => [
                'total' => $expensesAmount,
            ],
            'kas' => [
                'uang_masuk'  => $uangMasuk,
                'uang_keluar' => $uangKeluar,
                'selisih_kas' => $selisihKas,
            ],

            // key lama (optional compatibility)
            'total_sales' => $salesAmount,
            'total_paid'  => $uangMasuk,
            'expenses'    => $expensesAmount,
            'purchases'   => $purchasesAmount,
            'cash_out'    => $uangKeluar,
            'cash_diff'   => $selisihKas,

            // tambahan eksplisit
            'profit_amount'            => $profitAmount,
            'payments_received_amount' => $uangMasuk,
            'payments_sent_amount'     => $uangKeluar,
            'payments_net_amount'      => $selisihKas,
        ];
    }
}
