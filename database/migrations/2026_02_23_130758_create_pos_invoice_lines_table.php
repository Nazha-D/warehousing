<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_invoice_lines', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pos_invoice_id')
                ->constrained('pos_invoices')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('discount_id')
                ->constrained()
                ->restrictOnDelete();

            // Snapshot Pricing
            $table->decimal('unit_price', 18, 4);
            $table->decimal('quantity', 18, 4);
            $table->decimal('discount_value', 18, 4)->default(0);
            $table->decimal('custom_discount_value', 18, 4)->default(0);

            $table->decimal('tax_value', 18, 4)->default(0);

            // Calculated at time of invoice
            $table->decimal('line_total', 18, 4);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_invoice_lines');
    }
};
