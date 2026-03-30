<?php

/**
 * ============================================
 * Migration: create_sales_orders_table
 * ============================================
 * NOTE:
 * - Field names are kept EXACTLY as provided
 * - No renaming, no semantic changes
 * - Status values are controlled via Enum (not DB enum)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            // Company & relations
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('price_list_id')->nullable();
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->unsignedBigInteger('salesperson_id')->nullable();
            $table->unsignedBigInteger('commission_method_id')->nullable();
            $table->unsignedBigInteger('cashing_method_id')->nullable();
            $table->unsignedBigInteger('company_header_id')->nullable();

            // Identity
            $table->string('sales_order_number')->unique();
            $table->string('reference')->nullable();
            $table->string('code')->nullable();
            $table->string('title')->nullable();

            // Dates
            $table->date('input_date')->nullable();
            $table->date('validity')->nullable();

            // Discounts
            $table->decimal('special_discount', 15, 4)->default(0);
            $table->decimal('special_discount_amount', 15, 4)->default(0);
            $table->decimal('global_discount', 15, 4)->default(0);
            $table->decimal('global_discount_amount', 15, 4)->default(0);

            // VAT & pricing flags
            $table->decimal('vat', 15, 4)->default(0);
            $table->decimal('vat_lebanese', 15, 4)->default(0);
            $table->boolean('vat_exempt')->default(false);
            $table->boolean('vat_inclusive_prices')->default(false);
            $table->boolean('before_vat_prices')->default(false);

            // Totals
            $table->decimal('total_before_vat', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
            $table->decimal('commission_rate', 15, 4)->default(0);
            $table->decimal('commission_total', 15, 4)->default(0);

            // Printing flags
            $table->boolean('not_printed')->default(false);
            $table->boolean('printed_as_vat_exempt')->default(false);
            $table->boolean('printed_as_percentage')->default(false);

            // Status
            $table->string('status');

            // Text
            $table->text('terms_and_conditions')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};

