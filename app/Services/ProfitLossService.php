<?php

namespace App\Services;

use Modules\Expense\Entities\Expense;
use Modules\Purchase\Entities\Purchase;
use Modules\PurchasesReturn\Entities\PurchaseReturn;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SalePayment;
use Modules\SalesReturn\Entities\SaleReturn;
use Modules\SalesReturn\Entities\SaleReturnPayment;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\PurchasesReturn\Entities\PurchaseReturnPayment;

class ProfitLossService
{
    public static function summary($startDate, $endDate): array
    {
        // === PENJUALAN ===
        $totalSalesCount = Sale::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->count();

        $salesAmount = Sale::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('total_amount') / 100;

        // === RETURN PENJUALAN ===
        $totalSaleReturnsCount = SaleReturn::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->count();

        $saleReturnsAmount = SaleReturn::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('total_amount') / 100;

        // === PEMBELIAN ===
        $totalPurchasesCount = Purchase::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->count();

        $purchasesAmount = Purchase::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('total_amount') / 100;

        // === RETURN PEMBELIAN (biar sama kayak Livewire) ===
        $totalPurchaseReturnsCount = PurchaseReturn::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->count();

        $purchaseReturnsAmount = PurchaseReturn::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('total_amount') / 100;

        // === BIAYA OPERASIONAL ===
        $expensesAmount = Expense::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        // === KEUNTUNGAN (SAMAKAN dengan Livewire calculateProfit) ===
        $revenue = $salesAmount - $saleReturnsAmount;

        $productCosts = 0;
        $sales = Sale::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->with(['saleDetails.product'])
            ->get();

        foreach ($sales as $sale) {
            foreach ($sale->saleDetails as $detail) {
                // Prioritaskan harga histori transaksi (product_cost), jika null jatuh ke harga master product terbaru
                $cost = $detail->product_cost ?? ($detail->product ? $detail->product->product_cost : 0);
                $productCosts += (float) $cost * (float) $detail->quantity;
            }
        }

        // --- KURANGI HPP DARI BARANG YANG DIRETUR ---
        $saleReturns = SaleReturn::completed()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->with(['saleReturnDetails.product'])
            ->get();

        foreach ($saleReturns as $return) {
            foreach ($return->saleReturnDetails as $detail) {
                $cost = $detail->product_cost ?? ($detail->product ? $detail->product->product_cost : 0);
                $productCosts -= (float) $cost * (float) $detail->quantity;
            }
        }

        $profitAmount = $revenue - $productCosts;

        // --- DUAL LABA: Kotor & Bersih ---
        $labaKotor  = $profitAmount; // Omset - HPP
        $labaBersih = $labaKotor - $expensesAmount;

        // === UANG MASUK (Berbasis Pembayaran Tunai Nyata) ===
        $salePayments = SalePayment::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        $purchaseReturnPayments = PurchaseReturnPayment::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        $uangMasuk = $salePayments + $purchaseReturnPayments;

        // === UANG KELUAR (Berbasis Pembayaran Tunai Nyata) ===
        $purchasePayments = PurchasePayment::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        $saleReturnPayments = SaleReturnPayment::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('amount') / 100;

        $uangKeluar = $purchasePayments + $saleReturnPayments + $expensesAmount;

        // SELISIH KAS
        $selisihKas = $uangMasuk - $uangKeluar;

        // === PIUTANG & HUTANG (UNTUK PDF) ===
        $piutang = Sale::completed()
            ->whereIn('payment_status', ['Partial', 'Unpaid'])
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('due_amount') / 100;

        $hutang = Purchase::completed()
            ->whereIn('payment_status', ['Partial', 'Unpaid'])
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->sum('due_amount') / 100;

        return [
            'penjualan' => [
                'trx_count'   => $totalSalesCount,
                'total_sales' => $salesAmount,
            ],
            'keuntungan' => [
                'profit'       => $profitAmount,
                'laba_kotor'   => $labaKotor,
                'laba_bersih'  => $labaBersih,
            ],
            'pembelian' => [
                'trx_count' => $totalPurchasesCount,
                'total'     => $purchasesAmount,
            ],
            'retur_penjualan' => [
                'trx_count' => $totalSaleReturnsCount,
                'total'     => $saleReturnsAmount,
            ],
            'retur_pembelian' => [
                'trx_count' => $totalPurchaseReturnsCount,
                'total'     => $purchaseReturnsAmount,
            ],
            'biaya' => [
                'total' => $expensesAmount,
            ],
            'kas' => [
                'uang_masuk'  => $uangMasuk,
                'uang_keluar' => $uangKeluar,
                'selisih_kas' => $selisihKas,
            ],

            // key lama
            'total_sales' => $salesAmount,
            'total_paid'  => $uangMasuk,
            'expenses'    => $expensesAmount,
            'purchases'   => $purchasesAmount,
            'cash_out'    => $uangKeluar,
            'cash_diff'   => $selisihKas,

            // tambahan eksplisit
            'profit_amount'            => $profitAmount,
            'laba_kotor'               => $labaKotor,
            'laba_bersih'              => $labaBersih,
            'payments_received_amount' => $uangMasuk,
            'payments_sent_amount'     => $uangKeluar,
            'payments_net_amount'      => $selisihKas,
            'piutang'                  => $piutang,
            'hutang'                   => $hutang,
        ];
    }
}
