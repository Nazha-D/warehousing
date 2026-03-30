<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_group_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_group_id')->nullable()->constrained('item_groups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('order_index')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_group_item');
    }
};
