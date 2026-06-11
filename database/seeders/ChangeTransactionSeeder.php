<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Entities\Product;
use Modules\Purchase\Entities\Purchase;
use Modules\Purchase\Entities\PurchaseDetail;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\SalePayment;
use Carbon\Carbon;

class ChangeTransactionSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $products = Product::all();

        if ($products->count() == 0) {
            $this->command->warn('Tidak ada produk! Silakan jalankan DummyTransactionSeeder terlebih dahulu.');
            return;
        }

        $this->command->info('Membuat 5 Data Penjualan (Customer: Umum) dengan Kembalian...');
        for ($i = 0; $i < 5; $i++) {
            $saleDate = $now->copy()->subDays(rand(1, 30))->format('Y-m-d');

            $keys = $products->random(rand(1, 3));
            $totalSale = 0;

            foreach ($keys as $pid) {
                // Harga satuan x Qty
                $qty = rand(1, 3);
                $totalSale += ($pid->product_price * $qty);
            }

            // Kembalian Acak
            $kembalian = array_rand(array_flip([10000, 20000, 50000, 100000]));
            $paidAmount = $totalSale + $kembalian;
            $dueAmount = $totalSale - $paidAmount;

            // Simpan Penjualan Master
            $sale = Sale::create([
                'date' => $saleDate,
                'reference' => 'SL-CHG/2026/0' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'customer_id' => null,
                'customer_name' => 'Umum',

                'tax_percentage' => 0,
                'tax_amount' => 0,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'total_amount' => $totalSale * 100,
                'paid_amount' => $paidAmount * 100,
                'due_amount' => $dueAmount * 100,

                'status' => 'Completed',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
                'note' => 'Penjualan Uang Lebih (Kembalian Rp ' . number_format($kembalian, 0, ',', '.') . ')',
            ]);

            // Simpan Item Detail
            foreach ($keys as $pid) {
                $qty = rand(1, 3);

                // MURNI HARGA SATUAN (bukan dikali quantity)
                $purePrice = $pid->product_price * 100;
                $subTotal = ($pid->product_price * $qty) * 100;

                SaleDetails::create([
                    'sale_id' => $sale->id,
                    'product_id' => $pid->id,
                    'product_name' => $pid->product_name,
                    'product_code' => $pid->product_code,
                    'quantity' => $qty,
                    'price' => $purePrice,
                    'unit_price' => $purePrice,
                    'sub_total' => $subTotal,

                    'product_discount_amount' => 0,
                    'product_discount_type' => 'fixed',
                    'product_tax_amount' => 0,
                    'product_cost' => ($pid->product_cost * 100)
                ]);
            }

            // Rekalkulasi Tagihan (Koreksi)
            $actualTotal = $sale->saleDetails()->sum('sub_total') / 100;
            $actualPaid = $actualTotal + $kembalian;
            $actualDue = $actualTotal - $actualPaid;

            $sale->update([
                'total_amount' => $actualTotal * 100,
                'paid_amount' => $actualPaid * 100,
                'due_amount' => $actualDue * 100,
            ]);

            SalePayment::create([
                'date' => $sale->date,
                'reference' => 'INV/' . $sale->reference,
                'amount' => $actualPaid,
                'sale_id' => $sale->id,
                'payment_method' => 'Cash'
            ]);
        }


        $this->command->info('Membuat 5 Data Pembelian (Supplier: Umum) dengan Kembalian...');
        for ($i = 0; $i < 5; $i++) {
            $purchaseDate = $now->copy()->subDays(rand(1, 30))->format('Y-m-d');
            $keys = $products->random(rand(2, 4));

            // Simpan data bayangan dulu
            $purchase = Purchase::create([
                'date' => $purchaseDate,
                'reference' => 'PR-CHG/2026/0' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'supplier_id' => null,
                'supplier_name' => 'Supplier Umum',
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,

                'total_amount' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,

                'status' => 'Completed',
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
                'note' => '',
            ]);

            // Item Detail (dan hitung total riil)
            foreach ($keys as $prod) {
                $qty = rand(5, 15);

                $purePrice = $prod->product_cost * 100;
                $subTotal = ($prod->product_cost * $qty) * 100;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $prod->id,
                    'product_name' => $prod->product_name,
                    'product_code' => $prod->product_code,
                    'quantity' => $qty,
                    'price' => $purePrice,
                    'unit_price' => $purePrice,
                    'sub_total' => $subTotal,
                    'product_discount_amount' => 0,
                    'product_discount_type' => 'fixed',
                    'product_tax_amount' => 0,
                ]);
            }

            // Rekalkulasi Tagihan 
            $actualTotal = $purchase->purchaseDetails()->sum('sub_total') / 100;
            $kembalian = array_rand(array_flip([10000, 50000, 100000]));

            $actualPaid = $actualTotal + $kembalian;
            $actualDue = $actualTotal - $actualPaid;

            $purchase->update([
                'total_amount' => $actualTotal * 100,
                'paid_amount' => $actualPaid * 100,
                'due_amount' => $actualDue * 100,
                'note' => 'Pembelian Uang Lebih (Kembalian Rp ' . number_format($kembalian, 0, ',', '.') . ')'
            ]);

            PurchasePayment::create([
                'date' => $purchaseDate,
                'reference' => 'INV/' . $purchase->reference,
                'amount' => $actualPaid,
                'purchase_id' => $purchase->id,
                'payment_method' => 'Cash'
            ]);
        }

        $this->command->info('==== Selesai: Bug Harga Satuan/Subtotal Terperbaiki ====');
    }
}
