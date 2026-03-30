<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
Schema::create('stock_movements', function (Blueprint $table) {
$table->id();

$table->foreignId('company_id')
->constrained()
->cascadeOnDelete()
->cascadeOnUpdate();

$table->foreignId('warehouse_id')
->constrained()
->cascadeOnDelete()
->cascadeOnUpdate();

$table->foreignId('item_id')
->constrained()
->cascadeOnDelete()
->cascadeOnUpdate();
    $table->foreignId('package_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();
$table->decimal('quantity', 15, 4);
$table->string('type');//opening_balance,adjustment,transfer,
$table->string('reference_type')->nullable();//invoice,purchase_order,stock_count
$table->unsignedBigInteger('reference_id')->nullable();
    $table->unsignedBigInteger('reference_line_id')->nullable();
    $table->unsignedBigInteger('reversed_from_id')->nullable();

$table->timestamp('occurred_at')->useCurrent();

$table->timestamps();

    $table->unique(
        ['reference_type', 'reference_id', 'item_id', 'warehouse_id','reference_line_id'],
        'stock_movements_ref_unique'
    );
});
}

public function down(): void
{
Schema::dropIfExists('stock_movements');
}
};
