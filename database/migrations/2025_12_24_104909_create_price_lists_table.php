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
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('name');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('price_lists')
                ->nullOnDelete();

            $table->boolean('is_dynamic')->default(true);

            $table->foreignId('currency_id')->constrained();
            $table->unsignedInteger('priority')->default(0);

            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->unique(['company_id','name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};
