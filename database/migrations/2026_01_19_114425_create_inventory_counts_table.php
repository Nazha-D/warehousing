<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->date('count_date');
            $table->decimal('counted_quantity', 14, 4);
            $table->decimal('difference', 14, 4)->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'warehouse_id', 'item_id', 'count_date'], 'unique_inventory_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_counts');
    }
};
