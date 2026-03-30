<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
//            $table->foreignId('sales_invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->string('delivery_number');
            $table->string('reference')->nullable();

            $table->date('date');
            $table->date('expected_delivery')->nullable();

            $table->decimal('total', 14, 4)->default(0);
            $table->string('pod_file_path')->nullable();
            $table->string('reason')->nullable();

            $table->string('status'); // processing | delivered | completed | delivery_failed | rejected | canceled

            $table->softDeletes();
            $table->timestamps();
            $table->unique(['delivery_number','company_id']);
            $table->index(['company_id', 'status']);
            $table->index(['client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
