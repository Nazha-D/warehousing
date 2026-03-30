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
        Schema::create('price_list_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('price_list_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('apply_on');
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('item_id')->nullable()->constrained();

            $table->string('base_source');
            $table->string('computation_method');
            $table->decimal('value', 15, 4);

            // Versioning / Audit
            $table->dateTime('start_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('end_date')->nullable();

            $table->unsignedInteger('priority')->default(0);
            $table->unique(
                ['price_list_id', 'apply_on', 'category_id', 'item_id', 'start_date'],
                'plr_composite_unique' // Custom shorter index name
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_list_rules');
    }
};
