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
        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('description')->nullable();

            $table->text('img_path')->nullable();
            $table->decimal('total')->nullable();
            $table->boolean('active')->nullable()->default(true);
           $table->unique(['company_id','code']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
