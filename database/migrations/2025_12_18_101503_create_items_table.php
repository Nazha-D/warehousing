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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('item_type_id')->nullable()->constrained('item_types')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->cascadeOnDelete()->nullOnDelete();
            $table->foreignId('pos_currency_id')->nullable()->constrained('currencies')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('price_currency_id')->nullable()->constrained('currencies')->cascadeOnDelete()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->nullOnDelete();
            $table->string('main_code')->nullable();
            $table->boolean('auto_generated_code')->default(true);
            $table->string('item_name');
            $table->boolean('print_main_code')->nullable();
            $table->string('short_description')->nullable();
            $table->boolean('show_on_pos')->nullable()->default(0);
            $table->string('main_description')->nullable();
            $table->string('second_language_description')->nullable();
            $table->foreignId('taxation_group_id')->nullable()->constrained('taxation_groups')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('subref_id')->nullable()->constrained('subrefs')->cascadeOnUpdate()->nullOnDelete();
            $table->boolean('can_be_sold')->nullable()->default(true);
            $table->boolean('can_be_purchased')->nullable()->default(true);
            $table->boolean('warranty')->nullable()->default(true);
            $table->date('last_allowed_purchase_date')->nullable();
            $table->double('unit_cost')->nullable()->default(0);
            $table->integer('decimal_cost')->nullable();
            $table->double('unit_price')->nullable();
            $table->integer('decimal_price')->nullable();
            $table->double('line_discount_limit')->nullable();
            $table->foreignId('package_id')->nullable()->constrained('packages')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('default_transaction_package_id')->nullable()->constrained('packages')->cascadeOnUpdate()->nullOnDelete();
            $table->string('package_unit_name')->nullable();
            $table->string('package_unit_quantity')->nullable();
            $table->string('package_set_name')->nullable();
            $table->string('package_set_quantity')->nullable();
            $table->string('package_superset_name')->nullable();
            $table->string('package_superset_quantity')->nullable();
            $table->string('package_palette_name')->nullable();
            $table->string('package_palette_quantity')->nullable();
            $table->string('package_container_name')->nullable();
            $table->string('package_container_quantity')->nullable();
            $table->integer('decimal_quantity')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('volume', 8, 2)->nullable();
            $table->boolean('discontinued')->nullable()->default(false);
            $table->boolean('blocked')->nullable()->default(false);
            $table->boolean('active')->nullable()->default(true);
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['item_type_id']);
            $table->dropForeign(['package_id']);
            $table->dropForeign(['currency_id']);
        });

        Schema::dropIfExists('items');
    }
};
