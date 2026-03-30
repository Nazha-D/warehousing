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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('warehouse_number');
            $table->string('name');

            $table->string('type')->default('main');
            $table->text('address')->nullable();
            $table->boolean('blocked')->default(true);
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'warehouse_number']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
