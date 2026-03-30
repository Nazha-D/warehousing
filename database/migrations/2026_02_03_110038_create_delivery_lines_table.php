<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_id')
                ->constrained()
                ->cascadeOnDelete();

            // هذا هو الربط الحقيقي
            $table->foreignId('stock_reservation_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('line_type_id')->constrained();
            $table->foreignId('item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('qty', 14, 4);
            $table->unsignedBigInteger('combo_id')->nullable();


            $table->text('description')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('package_name')->nullable();

            $table->string('image')->nullable();
            $table->string('note')->nullable();
            $table->decimal('invoiced_qty', 14, 4);

            $table->timestamps();

            $table->index(['delivery_id']);
            $table->index(['stock_reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_lines');
    }
};
