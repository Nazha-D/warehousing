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
        Schema::create('taxation_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->boolean('active')->nullable()->default(true);
            $table->timestamps();
            $table->unique(['company_id','code']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxation_groups');
    }
};
