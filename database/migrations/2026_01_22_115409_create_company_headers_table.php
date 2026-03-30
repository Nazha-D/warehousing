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
        Schema::create('company_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('default_quotation_currency_id')->nullable()->constrained('currencies')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('header_name')->nullable();
            $table->unique(['company_id','header_name']);
            $table->string('logo')->nullable();
            $table->string('full_company_name')->nullable();
            $table->string('address')->nullable();
            $table->string('mobile_code')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('trn')->nullable();
            $table->text('bank_info')->nullable();
            $table->decimal('vat')->nullable();
            $table->boolean('company_subject_to_vat')->default(true);
            $table->string('local_payments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_headers');
    }
};
