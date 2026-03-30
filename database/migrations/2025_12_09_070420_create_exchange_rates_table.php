<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('from_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 20, 6);
            $table->enum('source_type', ['AUTO','MANUAL'])->default('AUTO');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // يضمن لكل شركة أو generic rate عدم التكرار لنفس الزوج
            $table->unique(['company_id','from_currency_id','to_currency_id'], 'unique_rate_per_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
