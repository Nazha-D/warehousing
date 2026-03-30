<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->id();


// Relations
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('line_type_id')->nullable();


// Item line
            $table->unsignedBigInteger('item_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('discount', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);


// Combo line
            $table->unsignedBigInteger('combo_id')->nullable();

// Misc line types
            $table->string('title')->nullable();
            $table->text('note')->nullable();
            $table->string('image')->nullable();


// Ordering
            $table->integer('order_index')->default(0);


            $table->timestamps();
            $table->softDeletes();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('sales_order_lines');
    }
};
