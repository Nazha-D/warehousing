<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id');
            $table->foreignId('payment_term_id')->nullable();
//            $table->foreignId('sales_order_id')->nullable();
            $table->foreignId('price_list_id')->nullable();
            $table->foreignId('currency_id')->nullable();
            $table->foreignId('salesperson_id')->nullable();
            $table->foreignId('commission_method_id')->nullable();
            $table->foreignId('cashing_method_id')->nullable();
            $table->foreignId('client_id');

            $table->string('sales_invoice_number')->nullable();
            $table->string('reference')->nullable();

            $table->date('value_date')->nullable();
            $table->text('terms_and_conditions')->nullable();

            $table->decimal('commission_rate', 8, 2)->nullable()->default(0);
            $table->decimal('commission_total', 15, 2)->nullable()->default(0);

            $table->decimal('special_discount', 15, 2)->nullable()->default(0);
            $table->decimal('special_discount_amount', 15, 2)->nullable()->default(0);

            $table->decimal('global_discount', 15, 2)->nullable()->default(0);
            $table->decimal('global_discount_amount', 15, 2)->nullable()->default(0);

            $table->decimal('vat_lebanese', 15, 2)->nullable()->default(0);
            $table->decimal('vat', 15, 2)->nullable()->default(0);

            $table->decimal('total_before_vat', 15, 2)->nullable()->default(0);
            $table->decimal('total', 15, 2)->nullable()->default(0);

            $table->boolean('vat_exempt')->default(false);
            $table->boolean('not_printed')->default(true);
            $table->boolean('printed_as_vat_exempt')->default(false);
            $table->boolean('printed_as_percentage')->default(false);
            $table->boolean('vat_inclusive_prices')->default(false);
            $table->boolean('before_vat_prices')->default(false);

            $table->string('status')->default('draft');
            $table->string('code')->nullable();
            $table->string('title')->nullable();

            $table->foreignId('delivered_from_warehouse_id')->nullable();

            $table->date('invoice_delivery_date')->nullable();
            $table->date('input_date')->nullable();

            $table->foreignId('company_header_id')->nullable();
            $table->string('invoice_type')->nullable();

            $table->foreignId('car_id')->nullable();
            $table->foreignId('terms_and_condition_id')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
