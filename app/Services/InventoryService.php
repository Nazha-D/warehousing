<?php

namespace App\Services;

use App\Models\InventoryCount;
use App\Models\Item;
use App\Models\StockMovement;

class InventoryService
{
    /**
     * Get paginated inventory snapshot as of a specific date
     *
     * @param int $companyId
     * @param int $warehouseId
     * @param string $date        // YYYY-MM-DD
     * @param array $options      // 'category_id', 'item_group_ids', 'per_page', 'search'
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getInventorySnapshot(
        int $companyId,
        int $warehouseId,
        string $date,
        array $options = []
    ) {
        // -----------------------------
        // Pagination options
        // -----------------------------
        $perPageDefault = 25;
        $perPageMax = 100;

        $perPage = min($options['per_page'] ?? $perPageDefault, $perPageMax);
        $isPaginated = isset($options['is_paginated'])
            ? (bool) $options['is_paginated']
            : true;

        $search = $options['search'] ?? null;
        $categoryId = $options['category_id'] ?? null;
        $itemGroupIds = $options['item_group_ids'] ?? [];
        $itemId = $options['item_id'] ?? null;

        // -----------------------------
        // Base Item Query
        // -----------------------------
        $query = Item::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->with([
                'barCodes:id,item_id,code'
            ]);

        // 🔹 اختيار منتج واحد (أولوية)
        if ($itemId) {
            $query->where('id', $itemId);
        } else {
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('main_code', 'like', "%{$search}%")
                        ->orWhere('item_name', 'like', "%{$search}%");
                });
            }

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if (!empty($itemGroupIds)) {
                $query->whereHas('itemGroups', function ($q) use ($itemGroupIds) {
                    $q->whereIn('item_groups.id', $itemGroupIds);
                });
            }
        }

        // -----------------------------
        // Fetch items (paginated or not)
        // -----------------------------
        if ($isPaginated) {
            $items = $query->paginate($perPage);
            $collection = $items->getCollection();
        } else {
            $collection = $query->get();
        }

        // -----------------------------
        // Compute quantities snapshot
        // -----------------------------
        $itemIds = $collection->pluck('id');

        $quantities = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('item_id', $itemIds)
            ->whereDate('occurred_at', '<=', $date)
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(quantity) as qty_on_hand')
            ->pluck('qty_on_hand', 'item_id');

        // -----------------------------
        // Attach quantities + packages
        // -----------------------------
        $collection = $collection->transform(function ($item) use ($quantities) {
            $baseQty = (float) ($quantities[$item->id] ?? 0);

            return [
                'id' => $item->id,
                'main_code' => $item->main_code,
                'item_name' => $item->item_name,
                'main_description' => $item->main_description,
                'unit_cost' => $item->unit_cost,
                'qty_as_of_date' => $baseQty,
                'barcodes' => $item->barCodes
                    ->pluck('code')
                    ->filter()          // removes null values
                    ->values()          // reindex array
                    ->toArray(),
                'packages' => PackageQuantityCalculator::getItemQuantitiesWithPackages(
                    $item->id,
                    (int) floor($baseQty),
                    $item->default_transaction_package_id
                ),
            ];
        });

        // -----------------------------
        // Return
        // -----------------------------
        if ($isPaginated) {
            $items->setCollection($collection);
            return $items;
        }

        return $collection;
    }

    /**
     * Save inventory counts (bulk)
     *
     * @param int $companyId
     * @param int $warehouseId
     * @param string $countDate // YYYY-MM-DD
     * @param int $userId
     * @param array $items // [['item_id'=>.., 'counted_quantity'=>.., 'notes'=>..], ...]
     * @return void
     */
    public static function saveInventoryCounts(int $companyId, int $warehouseId, string $countDate, int $userId, array $items)
    {
        $itemIds = collect($items)->pluck('item_id')->toArray();

        // 1️⃣ جلب الرصيد المحسوب من Inventory Snapshot
        $quantities = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('item_id', $itemIds)
            ->whereDate('occurred_at', '>=', $countDate)
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(quantity) as system_qty')
            ->pluck('system_qty', 'item_id')
            ->toArray();

        // 2️⃣ إعداد البيانات للحفظ
        $records = [];
        foreach ($items as $item) {
            $itemId = $item['item_id'];
            $countedQty = $item['counted_quantity'];
            $records[] = [
                'company_id' => $companyId,
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'count_date' => $countDate,
                'counted_quantity' => $countedQty,
                'difference' => $countedQty - ($quantities[$itemId] ?? 0),
                'user_id' => $userId,
                'notes' => $item['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 3️⃣ Bulk insert (update on duplicate key)
        foreach ($records as $record) {
            InventoryCount::updateOrCreate(
                [
                    'company_id' => $record['company_id'],
                    'warehouse_id' => $record['warehouse_id'],
                    'item_id' => $record['item_id'],
                    'count_date' => $record['count_date'],
                ],
                $record
            );
        }
    }
}
