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
        Schema::table('company_currencies', function (Blueprint $table) {

            $table->boolean('is_default')->default(false)->after('currency_id');

            $table->boolean('is_pos_currency')->default(false)->after('is_default');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_currencies', function (Blueprint $table) {


            $table->dropColumn([
                'is_default',
                'is_pos_currency'
            ]);
        });
    }
};
