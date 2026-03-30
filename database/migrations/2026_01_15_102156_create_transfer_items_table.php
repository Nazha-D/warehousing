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
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transfer_id')
                ->constrained('transfers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('item_id')
                ->constrained('items')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // Quantities sent
            $table->decimal('transferred_qty', 15, 4);
            $table->foreignId('transferred_qty_package_id')
                ->constrained('packages')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // Quantities received (filled later)
            $table->decimal('received_qty', 15, 4)->nullable();
            $table->foreignId('received_qty_package_id')
                ->nullable()
                ->constrained('packages')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('note')->nullable();

            $table->timestamps();

            $table->unique(['transfer_id', 'item_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
    }
};
