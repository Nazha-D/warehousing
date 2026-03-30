<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyCurrenciesTable extends Migration
{
    public function up(): void
    {
        Schema::create('company_currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'currency_id']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_currencies');
    }
}
