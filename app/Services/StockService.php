<?php

namespace App\Services;

use App\Constants\StockMovementTypes;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Support\Facades\DB;
use DomainException;

class StockService
{
    /**
     * إضافة منتج إلى مستودع (Opening / Manual / Initial)
     */
    public function addProductToWarehouse(
        Warehouse $warehouse,
        Item $item,
        float $quantity,
        string $type = StockMovementTypes::OPENING_BALANCE
    ): WarehouseItem {

        if ($quantity < 0) {
            throw new DomainException('Quantity must be zero or greater.');
        }

        return DB::transaction(function () use ($warehouse, $item, $quantity, $type) {

            // تأكيد وجود المنتج في المستودع
            $warehouseItem = WarehouseItem::firstOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'item_id'      => $item->id,
                ],
                [
                    'company_id' => $warehouse->company_id,
                    'active'     => true,
                ]
            );

            // تسجيل حركة مخزون فقط إذا في كمية
            if ($quantity > 0) {
                StockMovement::create([
                    'company_id'   => $warehouse->company_id,
                    'warehouse_id' => $warehouse->id,
                    'item_id'      => $item->id,
                    'quantity'     => $quantity,
                    'type'         => $type,
                    'occurred_at'  => now(),
                ]);
            }

            return $warehouseItem;
        });
    }

    /**
     * إضافة عدة منتجات دفعة واحدة (مع دمج الكميات)
     *
     * items = [
     *   ['item_id' => 1, 'quantity' => 5],
     *   ['item_id' => 1, 'quantity' => 2],
     *   ['item_id' => 3, 'quantity' => 4],
     * ]
     */
    public function addMultipleProductsToWarehouse(
        Warehouse $warehouse,
        array $items,
        string $type = StockMovementTypes::OPENING_BALANCE
    ): void {

        DB::transaction(function () use ($warehouse, $items, $type) {

            $groupedItems = collect($items)->groupBy('item_id');

            foreach ($groupedItems as $itemId => $rows) {
                $quantity = $rows->sum('quantity');

                if ($quantity <= 0) {
                    continue;
                }

                $item = Item::findOrFail($itemId);

                $this->addProductToWarehouse(
                    $warehouse,
                    $item,
                    $quantity,
                    $type
                );
            }
        });
    }

    /**
     * نقل كمية منتج من مستودع إلى مستودع آخر
     */
    public function transferProduct(
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        Item $item,
        float $quantity
    ): void {

        if ($quantity <= 0) {
            throw new DomainException('Quantity must be greater than zero.');
        }

        DB::transaction(function () use ($fromWarehouse, $toWarehouse, $item, $quantity) {

            // التحقق من الرصيد
            $currentBalance = $this->getStockBalance($fromWarehouse, $item);

            if ($currentBalance < $quantity) {
                throw new DomainException('Insufficient stock in source warehouse.');
            }

            // تأكيد وجود المنتج في مستودع الهدف
            WarehouseItem::firstOrCreate(
                [
                    'warehouse_id' => $toWarehouse->id,
                    'item_id'      => $item->id,
                ],
                [
                    'company_id' => $toWarehouse->company_id,
                    'active'     => true,
                ]
            );

            // حركة خروج
            StockMovement::create([
                'company_id'   => $fromWarehouse->company_id,
                'warehouse_id' => $fromWarehouse->id,
                'item_id'      => $item->id,
                'quantity'     => -$quantity,
                'type'         => StockMovementTypes::TRANSFER_OUT,
                'occurred_at'  => now(),
            ]);

            // حركة دخول
            StockMovement::create([
                'company_id'   => $toWarehouse->company_id,
                'warehouse_id' => $toWarehouse->id,
                'item_id'      => $item->id,
                'quantity'     => $quantity,
                'type'         => StockMovementTypes::TRANSFER_IN,
                'occurred_at'  => now(),
            ]);
        });
    }

    /**
     * نقل عدة منتجات بين مستودعين
     */
    public function transferMultipleProducts(
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        array $items
    ): void {

        DB::transaction(function () use ($fromWarehouse, $toWarehouse, $items) {

            foreach ($items as $row) {
                $item = Item::findOrFail($row['item_id']);
                $quantity = (float) $row['quantity'];

                $this->transferProduct(
                    $fromWarehouse,
                    $toWarehouse,
                    $item,
                    $quantity
                );
            }
        });
    }

    /**
     * الحصول على الرصيد الحالي لمنتج في مستودع
     */
    public function getStockBalance(Warehouse $warehouse, Item $item): float
    {
        return StockMovement::where('warehouse_id', $warehouse->id)
            ->where('item_id', $item->id)
            ->sum('quantity');
    }
    public static function getItemQtyOnHand(int $itemId): array
    {
        return StockMovement::selectRaw('warehouses.warehouse_number as warehouse_number, SUM(stock_movements.quantity) as qty_on_hand')
            ->join('warehouses', 'warehouses.id', '=', 'stock_movements.warehouse_id')
            ->where('stock_movements.item_id', $itemId)
            ->groupBy('warehouses.warehouse_number')
            ->pluck('qty_on_hand', 'warehouse_number')
            ->toArray();
    }
//    public static function getItemQtyOnHand(int $itemId)
//    {
//        return StockMovement::query()
//            ->selectRaw('
//            stock_movements.warehouse_id,
//            warehouses.warehouse_number,
//            SUM(stock_movements.quantity) as qty_on_hand
//        ')
//            ->join('warehouses', 'warehouses.id', '=', 'stock_movements.warehouse_id')
//            ->where('stock_movements.item_id', $itemId)
//            ->groupBy('stock_movements.warehouse_id', 'warehouses.warehouse_number')
//            ->get();
//    }
}
