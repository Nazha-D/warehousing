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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('salesperson_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
          //  $table->foreignId('payment_term_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
        //    $table->foreignId('price_list_id')->nullable()->after('salesperson_id')->constrained('price_lists')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('client_company_id')->nullable()->constrained('clients')->cascadeOnUpdate()->nullOnDelete();
            $table->string('client_number')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('street')->nullable();
            $table->string('job_position')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('mobile_code')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('email')->nullable();
            $table->string('reference')->nullable();
            $table->string('title')->nullable();
            $table->string('tags')->nullable();
            $table->decimal('granted_discount',15,4)->nullable();
            $table->string('tax_id')->nullable();
            $table->string('contact_type')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_country')->nullable();
            $table->string('contact_city')->nullable();
            $table->string('contact_state')->nullable();
            $table->string('contact_zip')->nullable();
            $table->string('contact_street')->nullable();
            $table->string('contact_phone_code')->nullable();
            $table->string('contact_phone_number')->nullable();
            $table->string('contact_mobile_code')->nullable();
            $table->string('contact_mobile_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('auto_generated_number')->default(true);
            $table->boolean('is_cash_customer')->default(false);
            $table->string('floor_and_building')->nullable();
            $table->string('website')->nullable();

            $table->string('note')->nullable();
            $table->boolean('show_on_pos')->nullable()->default(true);
            $table->boolean('is_blocked')->nullable()->default(false);

            $table->boolean('active')->nullable()->default(true);
          //  $table->boolean('active')->nullable()->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
