<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('pos_terminal_id')
                ->constrained('pos_terminals')
                ->restrictOnDelete();

            $table->foreignId('opened_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('closed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('session_number')->nullable();
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');

            $table->text('note')->nullable();
            $table->dateTime('opening_date')->nullable();
            $table->dateTime('closing_date')->nullable();

            $table->timestamps();
            $table->softDeletes();


            $table->index('session_number');
            $table->index('opening_date');
            $table->index('closing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
