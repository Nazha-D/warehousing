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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            // Ownership
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');

            // Identity
            $table->string('quotation_number')->unique();
            $table->string('reference')->nullable();
            $table->string('title')->nullable();

            // Parties
            $table->foreignId('client_id')->constrained();
            $table->foreignId('salesperson_id')->nullable()->constrained('users');

            // Commercial
            $table->foreignId('currency_id')->constrained();
            $table->foreignId('price_list_id')->nullable()->constrained();
            $table->foreignId('payment_term_id')->nullable()->constrained();

            // Dates
            $table->date('input_date');
            $table->date('validity')->nullable();

            // Totals

            $table->decimal('total_before_vat', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);

            // VAT & pricing flags
            $table->boolean('vat_inclusive_prices')->default(false);
            $table->boolean('vat_exempt')->default(false);
            $table->boolean('not_printed')->default(false);
            $table->boolean('printed_as_vat_exempt')->default(false);
            $table->boolean('printed_as_percentage')->default(false);
            $table->boolean('before_vat_prices')->default(false);
            $table->decimal('special_discount')->nullable();
            $table->decimal('special_discount_amount')->nullable();
            $table->decimal('global_discount')->nullable();
            $table->decimal('global_discount_amount')->nullable();
            $table->decimal('vat')->nullable();
            $table->decimal('vat_lebanese')->nullable();

            // Status
            $table->string('status')->index();

            // Meta
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('company_header_id')->nullable()->constrained();
            $table->foreignId('commission_method_id')->nullable()->constrained();
            $table->foreignId('cashing_method_id')->nullable()->constrained();
            $table->foreignId('terms_and_conditions_id')->nullable()->constrained();
            $table->foreignId('delivery_term_id')->nullable()->constrained();
            $table->decimal('commission_rate')->nullable();
            $table->decimal('commission_total')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
