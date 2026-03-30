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
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('name');

            $table->string('address')->nullable();

            $table->string('pos_number');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: pos_number per company
            $table->unique(['company_id', 'pos_number']);

            // Optional performance indexes
            $table->index('warehouse_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_terminals');
    }
};
