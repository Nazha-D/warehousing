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
        Schema::create('replenishments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('replenishment_number')->unique();
            $table->string('manual_reference')->nullable();
            $table->date('date');

            $table->enum('status', ['draft', 'confirmed', 'cancelled'])
                ->default('draft');

            $table->foreignId('created_by')
                ->constrained('users');

            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replenishments');
    }
};
