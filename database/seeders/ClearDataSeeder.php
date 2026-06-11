<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Expense\Entities\ExpenseCategory;
use Modules\Expense\Entities\Expense;
use Modules\Purchase\Entities\Purchase;
use Modules\Purchase\Entities\PurchaseDetail;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\PurchasesReturn\Entities\PurchaseReturn;
use Modules\PurchasesReturn\Entities\PurchaseReturnDetail;
use Modules\PurchasesReturn\Entities\PurchaseReturnPayment;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\SalePayment;
use Modules\SalesReturn\Entities\SaleReturn;
use Modules\SalesReturn\Entities\SaleReturnDetail;
use Modules\SalesReturn\Entities\SaleReturnPayment;

class ClearDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Category::truncate();
        Product::truncate();
        ExpenseCategory::truncate();

        Sale::truncate();
        SaleDetails::truncate();
        SalePayment::truncate();
        SaleReturn::truncate();
        SaleReturnDetail::truncate();
        SaleReturnPayment::truncate();

        Purchase::truncate();
        PurchaseDetail::truncate();
        PurchasePayment::truncate();
        PurchaseReturn::truncate();
        PurchaseReturnDetail::truncate();
        PurchaseReturnPayment::truncate();

        Expense::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Semua data kategori, produk, penjualan, pembelian, dan pengeluaran berhasil dihapus total!');
    }
}
