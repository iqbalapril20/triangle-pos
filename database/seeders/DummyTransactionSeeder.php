<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\People\Entities\Customer;
use Modules\People\Entities\Supplier;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Expense\Entities\Expense;
use Modules\Expense\Entities\ExpenseCategory;
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
use Carbon\Carbon;

class DummyTransactionSeeder extends Seeder
{
    public function run()
    {
        // ======================================================
        // 1. TRUNCATE SEMUA TABEL TRANSAKSI
        // ======================================================
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

        // ======================================================
        // 2. SETUP KATEGORI & PIHAK
        // ======================================================
        $cat = Category::create(['category_code' => 'CAT01', 'category_name' => 'Pakaian']);

        // Suppliers (sesuai spreadsheet)
        $suppliers = [];
        $supplierData = [
            'Supplier Basic Tee',
            'Supplier Aksesoris',
            'Supplier Footwear',
            'Supplier Outerwear',
            'Supplier Kemeja',
            'Supplier Denim',
        ];
        foreach ($supplierData as $i => $name) {
            $suppliers[$name] = Supplier::firstOrCreate(
                ['supplier_email' => strtolower(str_replace(' ', '', $name)) . '@test.com'],
                [
                    'supplier_name'  => $name,
                    'supplier_phone' => '0812' . str_pad($i + 1, 7, '0', STR_PAD_LEFT),
                    'city'           => 'Jakarta',
                    'country'        => 'Indonesia',
                    'address'        => 'Jl. Supplier ' . ($i + 1),
                ]
            );
        }

        // Customers (sesuai spreadsheet)
        $customers = [];
        $customerNames = ['Andi', 'Budi', 'Citra', 'Dedi', 'Eka', 'Fajar', 'Gita', 'Hadi', 'Intan', 'Joko', 'Kiki', 'Lina'];
        foreach ($customerNames as $i => $name) {
            $customers[$name] = Customer::firstOrCreate(
                ['customer_email' => strtolower($name) . '@test.com'],
                [
                    'customer_name'  => $name,
                    'customer_phone' => '0813' . str_pad($i + 1, 7, '0', STR_PAD_LEFT),
                    'city'           => 'Jakarta',
                    'country'        => 'Indonesia',
                    'address'        => 'Jl. Pelanggan ' . ($i + 1),
                ]
            );
        }

        $expCats = [];
        foreach (['Sewa', 'Utilitas', 'Gaji', 'Internet', 'Pemasaran'] as $ecName) {
            $expCats[$ecName] = ExpenseCategory::firstOrCreate(
                ['category_name' => $ecName],
                ['category_description' => 'Biaya ' . $ecName]
            );
        }

        // ======================================================
        // 3. MASTER BARANG (7 Produk sesuai spreadsheet)
        // ======================================================
        $productData = [
            ['code' => 'PRD-0001', 'nama' => 'Kaos Polos Hitam',  'cost' => 30000,  'price' => 50000,  'stok_awal' => 54],
            ['code' => 'PRD-0002', 'nama' => 'Kemeja Flannel',    'cost' => 80000,  'price' => 120000, 'stok_awal' => 90],
            ['code' => 'PRD-0003', 'nama' => 'Celana Jeans',      'cost' => 150000, 'price' => 275000, 'stok_awal' => 78],
            ['code' => 'PRD-0004', 'nama' => 'Jaket Hoodie',      'cost' => 95000,  'price' => 150000, 'stok_awal' => 68],
            ['code' => 'PRD-0005', 'nama' => 'Topi Baseball',     'cost' => 20000,  'price' => 45000,  'stok_awal' => 21],
            ['code' => 'PRD-0006', 'nama' => 'Sabuk Kulit',       'cost' => 40000,  'price' => 80000,  'stok_awal' => 34],
            ['code' => 'PRD-0007', 'nama' => 'Sepatu Sneakers',   'cost' => 150000, 'price' => 250000, 'stok_awal' => 10],
        ];

        $products = [];
        foreach ($productData as $p) {
            $prod = Product::create([
                'category_id'              => $cat->id,
                'product_name'             => $p['nama'],
                'product_code'             => $p['code'],
                'product_barcode_symbology' => 'C128',
                'product_quantity'         => $p['stok_awal'],
                'product_cost'             => $p['cost'],
                'product_price'            => $p['price'],
                'product_unit'             => 'PC',
                'product_stock_alert'      => 5,
            ]);
            $products[$p['code']] = $prod;
        }

        // === VARIABEL TRACKING UNTUK LAPORAN ===
        $summary = [
            'total_pembelian'   => 0,
            'total_penjualan'   => 0,
            'total_pengeluaran' => 0,
            'total_hpp'         => 0,
            'total_retur_jual'  => 0,
            'hpp_retur_jual'    => 0,
            'total_retur_beli'  => 0,
            'kas_masuk'         => 0, // SalePayments + PurchaseReturnPayments (Refund Kas)
            'kas_keluar'        => 0, // PurchasePayments + SaleReturnPayments (Refund Kas) + Expenses
            'piutang'           => 0,
            'hutang'            => 0,
        ];

        // ======================================================
        // 4. DATA PEMBELIAN (6 Transaksi)
        // ======================================================
        $purchaseData = [
            // [tgl, ref, supplier_name, product_code, qty, cost, nilai, paid, hutang, status]
            ['2026-03-01', 'PBL-001', 'Supplier Basic Tee',  'PRD-0001', 30, 30000,  900000,    900000,  0,       'Paid'],
            ['2026-03-02', 'PBL-002', 'Supplier Aksesoris',  'PRD-0005', 20, 20000,  400000,    0,       400000,  'Unpaid'],
            ['2026-03-03', 'PBL-003', 'Supplier Footwear',   'PRD-0007', 15, 150000, 2250000,   500000,  1750000, 'Partial'],
            ['2026-03-05', 'PBL-004', 'Supplier Outerwear',  'PRD-0004', 25, 95000,  2375000,   2375000, 0,       'Paid'],
            ['2026-03-07', 'PBL-005', 'Supplier Kemeja',     'PRD-0002', 20, 80000,  1600000,   600000,  1000000, 'Partial'],
            ['2026-03-09', 'PBL-006', 'Supplier Denim',      'PRD-0003', 10, 150000, 1500000,   1500000, 0,       'Paid'],
        ];

        $purchaseRecords = []; // Simpan untuk referensi retur
        foreach ($purchaseData as $pd) {
            [$tgl, $ref, $supplierName, $prodCode, $qty, $cost, $nilai, $paid, $hutang, $payStatus] = $pd;
            $supplier = $suppliers[$supplierName];
            $product  = $products[$prodCode];

            $purchase = Purchase::create([
                'date'                => $tgl,
                'reference'           => $ref,
                'supplier_id'         => $supplier->id,
                'supplier_name'       => $supplier->supplier_name,
                'tax_percentage'      => 0,
                'tax_amount'          => 0,
                'discount_percentage' => 0,
                'discount_amount'     => 0,
                'shipping_amount'     => 0,
                'total_amount'        => $nilai * 100,
                'paid_amount'         => $paid * 100,
                'due_amount'          => $hutang * 100,
                'status'              => 'Completed',
                'payment_status'      => $payStatus,
                'payment_method'      => 'Cash',
                'note'                => 'Pembelian stok ' . $product->product_name,
            ]);

            PurchaseDetail::create([
                'purchase_id'              => $purchase->id,
                'product_id'               => $product->id,
                'product_name'             => $product->product_name,
                'product_code'             => $product->product_code,
                'quantity'                 => $qty,
                'price'                    => $cost * 100,
                'unit_price'               => $cost * 100,
                'sub_total'                => $nilai * 100,
                'product_discount_amount'  => 0,
                'product_discount_type'    => 'fixed',
                'product_tax_amount'       => 0,
            ]);

            // Tambahkan stok produk
            $pModel = Product::find($product->id);
            $pModel->update(['product_quantity' => $pModel->product_quantity + $qty]);

            // Buat payment hanya jika ada uang dibayar
            if ($paid > 0) {
                PurchasePayment::create([
                    'date'           => $tgl,
                    'reference'      => 'PAY/' . $ref,
                    'amount'         => $paid,
                    'purchase_id'    => $purchase->id,
                    'payment_method' => 'Cash',
                ]);
                $summary['kas_keluar'] += $paid;
            }

            $summary['total_pembelian'] += $nilai;
            $summary['hutang'] += $hutang;
            $purchaseRecords[$ref] = $purchase;
        }

        // ======================================================
        // 5. DATA PENJUALAN (12 Transaksi)
        // ======================================================
        $salesData = [
            // [tgl, ref, customer, prodCode, qty, harga_jual, nilai, diterima, piutang, status, hpp_unit]
            ['2026-03-01', 'INV-001', 'Andi',  'PRD-0001', 12, 50000,  600000,   600000,  0,       'Paid',    30000],
            ['2026-03-02', 'INV-002', 'Budi',  'PRD-0002', 8,  120000, 960000,   0,       960000,  'Unpaid',  80000],
            ['2026-03-03', 'INV-003', 'Citra', 'PRD-0005', 10, 45000,  450000,   450000,  0,       'Paid',    20000],
            ['2026-03-04', 'INV-004', 'Dedi',  'PRD-0004', 6,  150000, 900000,   900000,  0,       'Paid',    95000],
            ['2026-03-05', 'INV-005', 'Eka',   'PRD-0003', 5,  275000, 1375000,  500000,  875000,  'Partial', 150000],
            ['2026-03-06', 'INV-006', 'Fajar', 'PRD-0006', 7,  80000,  560000,   560000,  0,       'Paid',    40000],
            ['2026-03-07', 'INV-007', 'Gita',  'PRD-0001', 15, 50000,  750000,   250000,  500000,  'Partial', 30000],
            ['2026-03-08', 'INV-008', 'Hadi',  'PRD-0002', 10, 120000, 1200000,  1200000, 0,       'Paid',    80000],
            ['2026-03-09', 'INV-009', 'Intan', 'PRD-0007', 6,  250000, 1500000,  500000,  1000000, 'Partial', 150000],
            ['2026-03-10', 'INV-010', 'Joko',  'PRD-0003', 4,  275000, 1100000,  1100000, 0,       'Paid',    150000],
            ['2026-03-11', 'INV-011', 'Kiki',  'PRD-0004', 8,  150000, 1200000,  300000,  900000,  'Partial', 95000],
            ['2026-03-12', 'INV-012', 'Lina',  'PRD-0005', 5,  45000,  225000,   225000,  0,       'Paid',    20000],
        ];

        $saleRecords = [];
        foreach ($salesData as $sd) {
            [$tgl, $ref, $custName, $prodCode, $qty, $harga, $nilai, $diterima, $piutang, $payStatus, $hppUnit] = $sd;
            $customer = $customers[$custName];
            $product  = $products[$prodCode];

            $sale = Sale::create([
                'date'                => $tgl,
                'reference'           => $ref,
                'customer_id'         => $customer->id,
                'customer_name'       => $customer->customer_name,
                'tax_percentage'      => 0,
                'tax_amount'          => 0,
                'discount_percentage' => 0,
                'discount_amount'     => 0,
                'shipping_amount'     => 0,
                'total_amount'        => $nilai * 100,
                'paid_amount'         => $diterima * 100,
                'due_amount'          => $piutang * 100,
                'status'              => 'Completed',
                'payment_status'      => $payStatus,
                'payment_method'      => 'Cash',
                'note'                => 'Penjualan ke ' . $custName,
            ]);

            SaleDetails::create([
                'sale_id'                 => $sale->id,
                'product_id'              => $product->id,
                'product_name'            => $product->product_name,
                'product_code'            => $product->product_code,
                'quantity'                => $qty,
                'price'                   => $harga * 100,
                'unit_price'              => $harga * 100,
                'sub_total'               => $nilai * 100,
                'product_discount_amount' => 0,
                'product_discount_type'   => 'fixed',
                'product_tax_amount'      => 0,
                'product_cost'            => $hppUnit * 100,
            ]);

            // Kurangi stok produk
            $pModel = Product::find($product->id);
            $pModel->update(['product_quantity' => $pModel->product_quantity - $qty]);

            // Buat SalePayment jika ada uang diterima
            if ($diterima > 0) {
                SalePayment::create([
                    'date'           => $tgl,
                    'reference'      => 'PAY/' . $ref,
                    'amount'         => $diterima,
                    'sale_id'        => $sale->id,
                    'payment_method' => 'Cash',
                ]);
                $summary['kas_masuk'] += $diterima;
            }

            $summary['total_penjualan'] += $nilai;
            $summary['total_hpp'] += ($hppUnit * $qty);
            $summary['piutang'] += $piutang;
            $saleRecords[$ref] = $sale;
        }

        // ======================================================
        // 6. RETUR PEMBELIAN (3 Transaksi)
        // ======================================================
        $purchaseReturnData = [
            // [tgl, ref, purchaseRef, supplierName, prodCode, qty, cost, nilai, dampak]
            ['2026-03-08', 'RTB-001', 'PBL-002', 'Supplier Aksesoris', 'PRD-0005', 2, 20000,  40000,  'Kurangi Hutang'],
            ['2026-03-10', 'RTB-002', 'PBL-003', 'Supplier Footwear',  'PRD-0007', 1, 150000, 150000, 'Kurangi Hutang'],
            ['2026-03-11', 'RTB-003', 'PBL-004', 'Supplier Outerwear', 'PRD-0004', 1, 95000,  95000,  'Refund Kas'],
        ];

        foreach ($purchaseReturnData as $prd) {
            [$tgl, $ref, $purchaseRef, $supplierName, $prodCode, $qty, $cost, $nilai, $dampak] = $prd;
            $supplier = $suppliers[$supplierName];
            $product  = $products[$prodCode];

            $isRefund = ($dampak === 'Refund Kas');
            $paidAmt  = $isRefund ? $nilai : 0;
            $dueAmt   = $isRefund ? 0 : $nilai;

            $purchaseReturn = PurchaseReturn::create([
                'date'                => $tgl,
                'reference'           => $ref,
                'supplier_id'         => $supplier->id,
                'supplier_name'       => $supplier->supplier_name,
                'tax_percentage'      => 0,
                'tax_amount'          => 0,
                'discount_percentage' => 0,
                'discount_amount'     => 0,
                'shipping_amount'     => 0,
                'total_amount'        => $nilai * 100,
                'paid_amount'         => $paidAmt * 100,
                'due_amount'          => $dueAmt * 100,
                'status'              => 'Completed',
                'payment_status'      => $isRefund ? 'Paid' : 'Unpaid',
                'payment_method'      => 'Cash',
                'note'                => 'Retur beli: ' . $dampak,
            ]);

            PurchaseReturnDetail::create([
                'purchase_return_id'       => $purchaseReturn->id,
                'product_id'               => $product->id,
                'product_name'             => $product->product_name,
                'product_code'             => $product->product_code,
                'quantity'                 => $qty,
                'price'                    => $cost * 100,
                'unit_price'               => $cost * 100,
                'sub_total'                => $nilai * 100,
                'product_discount_amount'  => 0,
                'product_discount_type'    => 'fixed',
                'product_tax_amount'       => 0,
            ]);

            // Kurangi stok produk (barang dikembalikan ke supplier)
            $pModel = Product::find($product->id);
            $pModel->update(['product_quantity' => $pModel->product_quantity - $qty]);

            // Jika "Refund Kas": uang masuk ke laci kasir
            if ($isRefund) {
                PurchaseReturnPayment::create([
                    'date'               => $tgl,
                    'reference'          => 'PAY/' . $ref,
                    'amount'             => $nilai,
                    'purchase_return_id' => $purchaseReturn->id,
                    'payment_method'     => 'Cash',
                ]);
                $summary['kas_masuk'] += $nilai;
            }

            // Jika "Kurangi Hutang": kurangi due_amount pada Purchase asal
            if ($dampak === 'Kurangi Hutang' && isset($purchaseRecords[$purchaseRef])) {
                $origPurchase = Purchase::find($purchaseRecords[$purchaseRef]->id);
                if ($origPurchase) {
                    $newDue = ($origPurchase->due_amount * 100) - ($nilai * 100);
                    $newPaid = ($origPurchase->paid_amount * 100);
                    $origPurchase->update([
                        'due_amount'     => max(0, $newDue),
                        'payment_status' => $newDue <= 0 ? 'Paid' : 'Partial',
                    ]);
                    $summary['hutang'] -= $nilai;
                }
            }

            $summary['total_retur_beli'] += $nilai;
        }

        // ======================================================
        // 7. RETUR PENJUALAN (3 Transaksi)
        // ======================================================
        $saleReturnData = [
            // [tgl, ref, saleRef, custName, prodCode, qty, harga, nilai, penyelesaian, hppUnit]
            ['2026-03-09', 'RTJ-001', 'INV-002', 'Budi',  'PRD-0002', 1, 120000, 120000, 'Potong Piutang', 80000],
            ['2026-03-10', 'RTJ-002', 'INV-003', 'Citra', 'PRD-0005', 1, 45000,  45000,  'Refund Kas',     20000],
            ['2026-03-12', 'RTJ-003', 'INV-010', 'Joko',  'PRD-0003', 1, 275000, 275000, 'Refund Kas',     150000],
        ];

        foreach ($saleReturnData as $srd) {
            [$tgl, $ref, $saleRef, $custName, $prodCode, $qty, $harga, $nilai, $penyelesaian, $hppUnit] = $srd;
            $customer = $customers[$custName];
            $product  = $products[$prodCode];

            $isRefund = ($penyelesaian === 'Refund Kas');
            $paidAmt  = $isRefund ? $nilai : 0;
            $dueAmt   = $isRefund ? 0 : $nilai;

            $saleReturn = SaleReturn::create([
                'date'                => $tgl,
                'reference'           => $ref,
                'customer_id'         => $customer->id,
                'customer_name'       => $customer->customer_name,
                'tax_percentage'      => 0,
                'tax_amount'          => 0,
                'discount_percentage' => 0,
                'discount_amount'     => 0,
                'shipping_amount'     => 0,
                'total_amount'        => $nilai * 100,
                'paid_amount'         => $paidAmt * 100,
                'due_amount'          => $dueAmt * 100,
                'status'              => 'Completed',
                'payment_status'      => $isRefund ? 'Paid' : 'Unpaid',
                'payment_method'      => 'Cash',
                'note'                => 'Retur jual: ' . $penyelesaian,
            ]);

            SaleReturnDetail::create([
                'sale_return_id'           => $saleReturn->id,
                'product_id'               => $product->id,
                'product_name'             => $product->product_name,
                'product_code'             => $product->product_code,
                'quantity'                 => $qty,
                'price'                    => $harga * 100,
                'unit_price'               => $harga * 100,
                'sub_total'                => $nilai * 100,
                'product_discount_amount'  => 0,
                'product_discount_type'    => 'fixed',
                'product_tax_amount'       => 0,
                'product_cost'             => $hppUnit * 100,
            ]);

            // Tambah kembali stok produk (barang kembali dari pelanggan)
            $pModel = Product::find($product->id);
            $pModel->update(['product_quantity' => $pModel->product_quantity + $qty]);

            // Jika "Refund Kas": uang keluar ke pelanggan
            if ($isRefund) {
                SaleReturnPayment::create([
                    'date'           => $tgl,
                    'reference'      => 'PAY/' . $ref,
                    'amount'         => $nilai,
                    'sale_return_id' => $saleReturn->id,
                    'payment_method' => 'Cash',
                ]);
                $summary['kas_keluar'] += $nilai;
            }

            // Jika "Potong Piutang": kurangi piutang pada Sale asal
            if ($penyelesaian === 'Potong Piutang' && isset($saleRecords[$saleRef])) {
                $origSale = Sale::find($saleRecords[$saleRef]->id);
                if ($origSale) {
                    $newDue  = ($origSale->due_amount * 100) - ($nilai * 100);
                    $origSale->update([
                        'due_amount'     => max(0, $newDue),
                        'payment_status' => $newDue <= 0 ? 'Paid' : 'Partial',
                    ]);
                    $summary['piutang'] -= $nilai;
                }
            }

            $summary['total_retur_jual'] += $nilai;
            $summary['hpp_retur_jual'] += ($hppUnit * $qty);
        }

        // ======================================================
        // 8. BIAYA OPERASIONAL (5 Transaksi)
        // ======================================================
        $expenseData = [
            ['2026-03-03', 'Sewa toko Maret',        'Sewa',      1500000],
            ['2026-03-05', 'Listrik & air',           'Utilitas',  350000],
            ['2026-03-10', 'Gaji pegawai mingguan',   'Gaji',      1200000],
            ['2026-03-10', 'Internet toko',           'Internet',  250000],
            ['2026-03-12', 'Iklan online',            'Pemasaran', 400000],
        ];

        foreach ($expenseData as $i => $ed) {
            [$tgl, $keterangan, $kategori, $nilai] = $ed;
            Expense::create([
                'date'        => $tgl,
                'reference'   => 'EXP/2026/0' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'category_id' => $expCats[$kategori]->id,
                'amount'      => $nilai,
                'details'     => $keterangan,
            ]);
            $summary['total_pengeluaran'] += $nilai;
            $summary['kas_keluar'] += $nilai;
        }
        // ======================================================
        // 9. PELUNASAN PIUTANG (4 Transaksi)
        //    Customer bayar sisa utangnya → SalePayment tambahan
        // ======================================================
        $pelunasanData = [
            // [tgl, saleRef, custName, jumlah]
            ['2026-03-11', 'INV-002', 'Budi',  500000],  // Pelunasan sebagian
            ['2026-03-12', 'INV-005', 'Eka',   400000],  // Pelunasan
            ['2026-03-12', 'INV-007', 'Gita',  300000],  // Pelunasan
            ['2026-03-12', 'INV-009', 'Intan', 600000],  // Pelunasan
        ];

        foreach ($pelunasanData as $i => $pl) {
            [$tgl, $saleRef, $custName, $jumlah] = $pl;

            $origSale = Sale::find($saleRecords[$saleRef]->id);
            if ($origSale) {
                SalePayment::create([
                    'date'           => $tgl,
                    'reference'      => 'PLN/' . $saleRef . '/' . ($i + 1),
                    'amount'         => $jumlah,
                    'sale_id'        => $origSale->id,
                    'payment_method' => 'Cash',
                ]);

                $newPaid = ($origSale->paid_amount * 100) + ($jumlah * 100);
                $newDue  = ($origSale->due_amount * 100) - ($jumlah * 100);
                $origSale->update([
                    'paid_amount'    => max(0, $newPaid),
                    'due_amount'     => max(0, $newDue),
                    'payment_status' => $newDue <= 0 ? 'Paid' : 'Partial',
                ]);

                $summary['kas_masuk'] += $jumlah;
                $summary['piutang'] -= $jumlah;
            }
        }

        // ======================================================
        // 10. PEMBAYARAN HUTANG (3 Transaksi)
        //     Toko bayar sisa hutang ke supplier → PurchasePayment tambahan
        // ======================================================
        $pembayaranHutangData = [
            // [tgl, purchaseRef, supplierName, jumlah]
            ['2026-03-10', 'PBL-002', 'Supplier Aksesoris', 200000],
            ['2026-03-11', 'PBL-003', 'Supplier Footwear',  1000000],
            ['2026-03-12', 'PBL-005', 'Supplier Kemeja',    700000],
        ];

        foreach ($pembayaranHutangData as $i => $ph) {
            [$tgl, $purchaseRef, $supplierName, $jumlah] = $ph;

            $origPurchase = Purchase::find($purchaseRecords[$purchaseRef]->id);
            if ($origPurchase) {
                PurchasePayment::create([
                    'date'           => $tgl,
                    'reference'      => 'PLN/' . $purchaseRef . '/' . ($i + 1),
                    'amount'         => $jumlah,
                    'purchase_id'    => $origPurchase->id,
                    'payment_method' => 'Cash',
                ]);

                $newPaid = ($origPurchase->paid_amount * 100) + ($jumlah * 100);
                $newDue  = ($origPurchase->due_amount * 100) - ($jumlah * 100);
                $origPurchase->update([
                    'paid_amount'    => max(0, $newPaid),
                    'due_amount'     => max(0, $newDue),
                    'payment_status' => $newDue <= 0 ? 'Paid' : 'Partial',
                ]);

                $summary['kas_keluar'] += $jumlah;
                $summary['hutang'] -= $jumlah;
            }
        }

        // ======================================================
        // 11. GENERATE LAPORAN REKAP (rekap_testing.md)
        // ======================================================
        $netRevenue    = $summary['total_penjualan'] - $summary['total_retur_jual'];
        $netHpp        = $summary['total_hpp'] - $summary['hpp_retur_jual'];
        $labaKotor     = $netRevenue - $netHpp;
        $labaBersih    = $labaKotor - $summary['total_pengeluaran'];
        $selisihKas    = $summary['kas_masuk'] - $summary['kas_keluar'];

        $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');

        $reportText = "
# Laporan Pengujian Akurasi Sistem E-POS
Data sesuai spreadsheet (Maret 2026).

## Ringkasan Data
| Jenis | Jumlah |
|:------|:-------|
| Produk | 7 Item |
| Transaksi Pembelian | 6 |
| Transaksi Penjualan | 12 |
| Retur Pembelian | 3 |
| Retur Penjualan | 3 |
| Biaya Operasional | 5 |

## Hitungan Pasti (*Source of Truth*)

### A. Laporan Laba Rugi (Akrual)
| Deskripsi | Nilai |
|:----------|:------|
| Total Omset (Penjualan) | {$fmt($summary['total_penjualan'])} |
| Retur Penjualan | ({$fmt($summary['total_retur_jual'])}) |
| **Net Revenue** | **{$fmt($netRevenue)}** |
| | |
| HPP Penjualan | {$fmt($summary['total_hpp'])} |
| HPP Retur Jual (dikembalikan) | ({$fmt($summary['hpp_retur_jual'])}) |
| **Net HPP** | **{$fmt($netHpp)}** |
| | |
| **Laba Kotor (Net Revenue - Net HPP)** | **{$fmt($labaKotor)}** |
| Biaya Operasional | ({$fmt($summary['total_pengeluaran'])}) |
| **Laba Bersih (Laba Kotor - Biaya)** | **{$fmt($labaBersih)}** |

### B. Arus Kas Riil (Basis Pembayaran Tunai)
| Deskripsi | Nilai |
|:----------|:------|
| **Uang Masuk (SalePayments + PurchReturnRefund)** | **{$fmt($summary['kas_masuk'])}** |
| **Uang Keluar (PurchPayments + SaleReturnRefund + Expenses)** | **{$fmt($summary['kas_keluar'])}** |
| **Selisih Kas (Uang Masuk - Keluar)** | **{$fmt($selisihKas)}** |

### C. Piutang & Hutang
| Deskripsi | Nilai |
|:----------|:------|
| Piutang (Sisa Tagihan Pelanggan) | {$fmt($summary['piutang'])} |
| Hutang (Sisa Tagihan ke Supplier) | {$fmt($summary['hutang'])} |

### D. Persediaan Akhir (Stok)
| Kode | Produk | Stok Akhir | Nilai Persediaan |
|:-----|:-------|:-----------|:-----------------|";

        foreach ($products as $code => $prod) {
            $pModel = Product::find($prod->id);
            $stok = $pModel->product_quantity;
            $nilaiPersediaan = $stok * $pModel->product_cost;
            $reportText .= "\n| {$code} | {$pModel->product_name} | {$stok} | {$fmt($nilaiPersediaan)} |";
        }

        $totalPersediaan = Product::all()->sum(fn($p) => $p->product_quantity * $p->product_cost);
        $reportText .= "\n| | **TOTAL** | | **{$fmt($totalPersediaan)}** |";

        $reportText .= "\n\n_Report ini di-generate mesin dari DummyTransactionSeeder_\n";

        File::put(base_path('rekap_testing.md'), trim($reportText));
        $this->command->info('Data Spreadsheet Berhasil Di-inject!');
        $this->command->info('File ringkasan hitungan tersimpan di: rekap_testing.md');
    }
}
