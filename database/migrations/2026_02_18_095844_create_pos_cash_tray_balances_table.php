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
        Schema::create('pos_cash_tray_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pos_cash_tray_id')
                ->constrained('pos_cash_trays')
                ->cascadeOnDelete();

            $table->foreignId('currency_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('opening_amount', 15, 2)->default(0);
            $table->decimal('expected_amount', 15, 2)->nullable();
            $table->decimal('declared_closing_amount', 15, 2)->nullable();
            $table->decimal('difference', 15, 2)->nullable();

            $table->timestamps();

            $table->unique(['pos_cash_tray_id', 'currency_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_cash_tray_balances');
    }
};
