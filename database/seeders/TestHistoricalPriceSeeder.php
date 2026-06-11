<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\SalePayment;
use Carbon\Carbon;
use App\Services\ProfitLossService;

class TestHistoricalPriceSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('=== MEMULAI TEST HISTORI HARGA ===');

        // 1. Ambil 1 produk (misal produk pertama yang pernah terjual)
        $detailLama = SaleDetails::first();
        if (!$detailLama) {
            $this->command->error('Tidak ada data penjualan untuk dites.');
            return;
        }

        $produk = Product::find($detailLama->product_id);

        $hargaModalLama = $produk->product_cost;
        $hargaJualLama = $produk->product_price;

        $this->command->info("Produk Terpilih: {$produk->product_name}");
        $this->command->info("Harga Modal Awal: {$hargaModalLama} | Harga Jual Awal: {$hargaJualLama}");

        // Cek Profit Keseluruhan SEBELUM diubah
        $summaryBefore = ProfitLossService::summary(null, null);
        $profitBefore = $summaryBefore['profit_amount'];
        $hppBefore = $summaryBefore['total_hpp'] ?? 0;

        $this->command->info("Total Profit SEBELUM Ubah Master: {$profitBefore}");

        // 2. Ubah Harga Master Produk secara Drastis
        $hargaModalBaru = $hargaModalLama + 50000; // Naik 50rb
        $hargaJualBaru = $hargaJualLama + 100000; // Naik 100rb

        $produk->update([
            'product_cost' => $hargaModalBaru,
            'product_price' => $hargaJualBaru
        ]);

        $this->command->info("\n--- HARGA MASTER PRODUK DIUBAH! ---");
        $this->command->info("Harga Modal Baru: {$hargaModalBaru} | Harga Jual Baru: {$hargaJualBaru}");

        // 3. Cek Profit Keseluruhan SETELAH diubah master TAPI belum ada transaksi baru
        $summaryAfterChange = ProfitLossService::summary(null, null);
        $profitAfterChange = $summaryAfterChange['profit_amount'];

        if ($profitBefore === $profitAfterChange) {
            $this->command->info("STATUS 1: AMAN \u{2705} (Perubahan Master Produk TIDAK merusak histori laba/rugi transaksi lama!)");
        } else {
            $this->command->error("STATUS 1: GAGAL \u{274C} (Profit berubah dari {$profitBefore} menjadi {$profitAfterChange}!)");
        }

        // 4. Lakukan 1 Transaksi Penjualan Baru dengan harga baru
        $this->command->info("\n--- MEMBUAT 1 TRANSAKSI BARU TRANSAKSI... ---");

        $sale = Sale::create([
            'date' => Carbon::now()->format('Y-m-d'),
            'reference' => 'SL-TEST-' . time(),
            'customer_name' => 'Pelanggan Test Harga',
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total_amount' => $hargaJualBaru * 100,
            'paid_amount' => $hargaJualBaru * 100,
            'due_amount' => 0,
            'status' => 'Completed',
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
            'note' => 'Penjualan Test Histori',
        ]);

        SaleDetails::create([
            'sale_id' => $sale->id,
            'product_id' => $produk->id,
            'product_name' => $produk->product_name,
            'product_code' => $produk->product_code,
            'quantity' => 1,
            'price' => $hargaJualBaru * 100,
            'unit_price' => $hargaJualBaru * 100,
            'sub_total' => $hargaJualBaru * 100,
            'product_discount_amount' => 0,
            'product_discount_type' => 'fixed',
            'product_tax_amount' => 0,
            'product_cost' => $hargaModalBaru * 100
        ]);

        SalePayment::create([
            'date' => $sale->date,
            'reference' => 'INV/' . $sale->reference,
            'amount' => $hargaJualBaru, // mutator otomatis *100
            'sale_id' => $sale->id,
            'payment_method' => 'Cash'
        ]);

        // Profit & Modal dari transaksi baru ini saja
        $expectProfitBaru = $hargaJualBaru - $hargaModalBaru; // Misal naik 100k jual, 50k modal = profit nambah 50k

        $this->command->info("Satu penjualan produk '{$produk->product_name}' dengan harga baru telah ditambahkan.");
        $this->command->info("Ekspektasi Penambahan Profit Baru: {$expectProfitBaru}");

        // 5. Cek Laba Rugi Akhir
        $summaryFinal = ProfitLossService::summary(null, null);
        $profitFinal = $summaryFinal['profit_amount'];

        $selisihLaba = $profitFinal - $profitAfterChange;

        $this->command->info("\nTotal Profit Akhir: {$profitFinal}");

        if ($selisihLaba == $expectProfitBaru) {
            $this->command->info("STATUS 2: AMAN \u{2705} (Transaksi lama menggunakan harga lama, transaksi baru menggunakan harga baru secara akurat!)");
        } else {
            $this->command->error("STATUS 2: GAGAL \u{274C} (Profit bertambah {$selisihLaba}, seharusnya {$expectProfitBaru})");
        }

        $this->command->info('=== TEST SELESAI ===');
    }
}
