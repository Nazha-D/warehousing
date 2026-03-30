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
        Schema::create('pos_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->foreignId('pos_invoice_id')
                ->constrained('pos_invoices')
                ->cascadeOnDelete();

            $table->foreignId('pos_session_id')
                ->constrained('pos_sessions')
                ->cascadeOnDelete();

            $table->foreignId('pos_cash_tray_id')
                ->nullable()
                ->constrained('pos_cash_trays')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('cashing_method_id')
                ->constrained('cashing_methods')
                ->restrictOnDelete();
            // cash, card, bank_transfer, etc.

            $table->decimal('amount', 18, 2);
            // مبلغ الدفع بعملته الفعلية

            $table->decimal('exchange_rate', 18, 6)->default(1);
            // سعر الصرف لحظة الدفع

            $table->decimal('amount_in_invoice_currency', 18, 2);
            // المبلغ بعد التحويل لعملة الفاتورة

            $table->decimal('amount_in_company_currency', 18, 2)->nullable();
            // اختياري – مفيد جدًا للتقارير الموحدة
            $table->enum('type', ['payment', 'refund']);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_payments');
    }
};
