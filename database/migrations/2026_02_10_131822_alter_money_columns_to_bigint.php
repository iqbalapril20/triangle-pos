<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PRODUCTS
        DB::statement("ALTER TABLE products
            MODIFY product_cost BIGINT NOT NULL DEFAULT 0,
            MODIFY product_price BIGINT NOT NULL DEFAULT 0
        ");

        // PURCHASES
        DB::statement("ALTER TABLE purchases
            MODIFY shipping_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY paid_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY total_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY due_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY tax_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY discount_amount BIGINT NOT NULL DEFAULT 0
        ");

        DB::statement("ALTER TABLE purchase_details
            MODIFY price BIGINT NOT NULL DEFAULT 0,
            MODIFY unit_price BIGINT NOT NULL DEFAULT 0,
            MODIFY sub_total BIGINT NOT NULL DEFAULT 0,
            MODIFY product_discount_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY product_tax_amount BIGINT NOT NULL DEFAULT 0
        ");

        DB::statement("ALTER TABLE purchase_payments
            MODIFY amount BIGINT NOT NULL DEFAULT 0
        ");

        // SALES
        DB::statement("ALTER TABLE sales
            MODIFY shipping_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY paid_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY total_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY due_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY tax_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY discount_amount BIGINT NOT NULL DEFAULT 0
        ");

        DB::statement("ALTER TABLE sale_details
            MODIFY price BIGINT NOT NULL DEFAULT 0,
            MODIFY unit_price BIGINT NOT NULL DEFAULT 0,
            MODIFY sub_total BIGINT NOT NULL DEFAULT 0,
            MODIFY product_discount_amount BIGINT NOT NULL DEFAULT 0,
            MODIFY product_tax_amount BIGINT NOT NULL DEFAULT 0
        ");

        DB::statement("ALTER TABLE sale_payments
            MODIFY amount BIGINT NOT NULL DEFAULT 0
        ");

        // EXPENSES (kalau ada di project kamu)
        if ($this->tableExists('expenses')) {
            DB::statement("ALTER TABLE expenses
                MODIFY amount BIGINT NOT NULL DEFAULT 0
            ");
        }

        // PURCHASE RETURNS (kalau ada)
        if ($this->tableExists('purchase_returns')) {
            DB::statement("ALTER TABLE purchase_returns
                MODIFY shipping_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY paid_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY total_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY due_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY tax_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY discount_amount BIGINT NOT NULL DEFAULT 0
            ");
        }

        if ($this->tableExists('purchase_return_details')) {
            DB::statement("ALTER TABLE purchase_return_details
                MODIFY price BIGINT NOT NULL DEFAULT 0,
                MODIFY unit_price BIGINT NOT NULL DEFAULT 0,
                MODIFY sub_total BIGINT NOT NULL DEFAULT 0,
                MODIFY product_discount_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY product_tax_amount BIGINT NOT NULL DEFAULT 0
            ");
        }

        if ($this->tableExists('purchase_return_payments')) {
            DB::statement("ALTER TABLE purchase_return_payments
                MODIFY amount BIGINT NOT NULL DEFAULT 0
            ");
        }

        // SALE RETURNS (kalau ada)
        if ($this->tableExists('sale_returns')) {
            DB::statement("ALTER TABLE sale_returns
                MODIFY shipping_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY paid_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY total_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY due_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY tax_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY discount_amount BIGINT NOT NULL DEFAULT 0
            ");
        }

        if ($this->tableExists('sale_return_details')) {
            DB::statement("ALTER TABLE sale_return_details
                MODIFY price BIGINT NOT NULL DEFAULT 0,
                MODIFY unit_price BIGINT NOT NULL DEFAULT 0,
                MODIFY sub_total BIGINT NOT NULL DEFAULT 0,
                MODIFY product_discount_amount BIGINT NOT NULL DEFAULT 0,
                MODIFY product_tax_amount BIGINT NOT NULL DEFAULT 0
            ");
        }

        if ($this->tableExists('sale_return_payments')) {
            DB::statement("ALTER TABLE sale_return_payments
                MODIFY amount BIGINT NOT NULL DEFAULT 0
            ");
        }
    }

    public function down(): void
    {
        // kalau mau rollback, kamu bisa isi balik ke INT,
        // tapi biasanya money lebih aman tetap BIGINT.
    }

    private function tableExists(string $table): bool
    {
        $db = DB::getDatabaseName();
        $result = DB::selectOne(
            "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
            [$db, $table]
        );
        return ((int)($result->c ?? 0)) > 0;
    }
};