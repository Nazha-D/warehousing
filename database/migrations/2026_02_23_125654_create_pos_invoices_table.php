<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_invoices', function (Blueprint $table) {

            $table->id();

//            $table->uuid('uuid')->unique();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_terminal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('pos_sessions')->cascadeOnDelete();

            $table->foreignId('pos_cash_tray_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finished_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();

            $table->decimal('exchange_rate', 18, 8)->default(1);

            $table->string('invoice_number')->index();

            $table->enum('status', [
                'DRAFT',
                'PARTIAL',
                'PAID',
                'REFUNDED',
                'CANCELLED'
            ])->default('DRAFT');

            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('tax_total', 18, 4)->default(0);
            $table->decimal('discount_total', 18, 4)->default(0);
            $table->decimal('custom_discount_total', 18, 4)->default(0);

            $table->decimal('grand_total', 18, 4)->default(0);

            $table->decimal('paid_total', 18, 4)->default(0);
            $table->decimal('remaining_total', 18, 4)->default(0);
            $table->decimal('change_total', 18, 4)->default(0);

            $table->boolean('stock_applied')->default(false);

            $table->text('note')->nullable();

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_invoices');
    }
};
