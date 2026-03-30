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
        Schema::create('combo_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->nullable()->constrained('combos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price')->nullable();
            $table->decimal('discount')->nullable();
            $table->decimal('total')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos_items');
    }
};
