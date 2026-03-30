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
        Schema::create('replenishment_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('replenishment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('package_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('quantity', 15, 4);

            $table->decimal('unit_cost', 15, 4)
                ->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['replenishment_id', 'item_id', 'package_id'],
                'replenishment_unique_line'
            );
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replenishment_lines');
    }
};
