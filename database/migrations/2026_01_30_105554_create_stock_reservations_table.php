<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();

            // Multi-tenant
            $table->unsignedBigInteger('company_id');

            // Core references
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('item_id');

            // Quantities
            $table->decimal('reserved_quantity', 15, 4);

            // Polymorphic source (SalesOrderLine today, others tomorrow)
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');

            // Lifecycle
            $table->string('status'); // active, consumed, cancelled, expired

            $table->timestamps();

            // Indexes
            $table->index(['company_id']);
            $table->index(['warehouse_id', 'item_id']);
            $table->index(['source_type', 'source_id']);
            $table->index(['status']);

            // Optional FKs (إذا كنتِ strict)
            // $table->foreign('warehouse_id')->references('id')->on('warehouses');
            // $table->foreign('item_id')->references('id')->on('items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
