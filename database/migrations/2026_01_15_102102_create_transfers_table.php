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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('sending_user_id')->nullable()->constrained('users')->restrictOnDelete()->cascadeOnUpdate();//user making the transfer out
            $table->foreignId('receiving_user_id')->nullable()->constrained('users')->restrictOnDelete()->cascadeOnUpdate();//user confirming transfer in
            $table->date('date');

            $table->string('transfer_number')->index();
            $table->string('manual_reference')->nullable();

            $table->foreignId('src_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('dest_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->enum('status', [
                'draft',
                'sent',
                'received',
                'cancelled'
            ])->default('draft');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'transfer_number']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
