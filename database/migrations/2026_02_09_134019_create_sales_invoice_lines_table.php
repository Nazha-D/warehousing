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
        Schema::create('sales_invoice_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_invoice_id');

            // الحقل الجديد الوحيد لخدمة التصميم الجديد
            $table->foreignId('delivery_line_id');
            $table->foreignId('line_type_id')->nullable();

            $table->foreignId('item_id')->nullable();

            $table->foreignId('combo_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);

            $table->text('note')->nullable();
            $table->text('image')->nullable();

            $table->integer('order_index')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_lines');
    }
};
