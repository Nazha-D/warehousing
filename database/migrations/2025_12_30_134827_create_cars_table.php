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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('car_brand_id')->constrained('car_brands')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('car_model_id')->constrained('car_models')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('car_color_id')->constrained('car_colors')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('car_technician_id')->constrained('car_technicians')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('plate_number');
            $table->string('chassis_number')->unique(); // globally unique
            $table->string('car_fax')->nullable(); // could be file path or reference
            $table->year('year');
            $table->enum('rating', ['VIP', 'Regular','Blocked'])->default('regular');
            $table->bigInteger('odometer')->default(0); // numeric (integer km/miles)
            $table->text('comment')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
