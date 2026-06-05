<?php

namespace App\Services;

use App\Constants\ItemConstants;
use App\Http\Resources\ItemResource;
use App\Models\AlternativeCode;
use App\Models\BarCode;
use App\Models\Item;
//use App\Models\Session;
//use App\Models\PosTerminal;
//use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\SupplierCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Image;

class ItemService
{
    /**
     * Get all items with flexible filters and pagination
     */
    public static function getAll(bool $canViewAllCompanies, int $userCompanyId, array $options = [])
    {
        $perPage = $options['perPage'] ?? 10;
        $isPaginated = $options['isPaginated'] ?? true;
        $onlyActive = $options['onlyActive'] ?? false;
        $posProducts = $options['posProducts'] ?? false;
        $withVirtual = $options['withVirtual'] ?? true;
        $barcode = $options['barcode'] ?? null;
        $search = $options['search'] ?? null;
        $searchByCat = $options['searchByCat'] ?? null;

        $items = Item::query();

        // Restrict by warehouse
//        if (!empty($options['warehouseId'])) {
//            $warehouse = Warehouse::findOrFail($options['warehouseId']);
//            $itemIds = $warehouse->stocks()->pluck('item_id');
//            $items->whereIn('id', $itemIds);
//        }

        // Restrict by company
        if (!$canViewAllCompanies) {
            $items->where('company_id', $userCompanyId);
        }

        // Filter by barcode
        if ($barcode) {
            $items->whereHas('barCodes', function ($query)use($barcode)
            {
                $query->where('code',$barcode);
            });
        }

        // Search filter
        if ($search) {
            $items->where(function ($query) use ($search) {
                $query->where('main_code', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%");

            });
        }
        if ($searchByCat) {
            $items->where(function ($query) use ($searchByCat) {
                $query->where('category_id', $searchByCat);

            });
        }

            // Only active products
            if ($onlyActive) {
                $items->where('active', true);
            }

            // POS products
            if ($posProducts) {
                $items->where('show_on_pos', true);
            }

            // Exclude virtual items
            if (!$withVirtual) {
                $items->whereHas('itemType', function ($q) {
                    $q->where('name', '!=', 'virtual');
                });
            }

            // Pagination
            return $isPaginated ? $items->paginate($perPage) : $items->get();

    }

    /**
     * Delete item image
     */
    public function deleteImage(string $image): void
    {
        Storage::disk('public')->delete($image);
    }

    /**
     * Save item image
     */
    public function saveImage(Item $item, string $attribute, $requestFile): void
    {
        if ($item->{$attribute}) {
            $this->deleteImage($item->{$attribute});
        }

        $image = Image::make($requestFile)
            ->orientate()
            ->resize(2000, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 85);

        $path = 'item-images/' . Str::uuid() . '.webp';

        Storage::disk('public')->put($path, (string) $image);

        $item->update([
            $attribute => $path,
        ]);
    }
    /**
     * Get item quantities in a warehouse
     */
    public static function getItemQuantitiesInWarehouse(int $itemId, int $warehouseId): array
    {
        $item = Item::findOrFail($itemId);
        $stock = $item->stockWarehouse($warehouseId);

        $qtyOnHand = $stock->qty_on_hand ?? 0;
        $qtyOnHandPackages = $stock ? self::getItemQuantitiesWithPackages($itemId, $qtyOnHand, $item->default_transaction_package_id) : null;

        return [
            'item' => new ItemResource($item),
            'qtyOnHand' => $qtyOnHand,
            'qtyOnHandPackages' => $qtyOnHandPackages,
        ];
    }

    /**
     * Convert quantity to package breakdown
     */
    public static function getItemQuantitiesWithPackages(int $itemId, int $quantity, int $packageId): array
    {
        $item = Item::findOrFail($itemId);

        $multipliers = [
            'set' => $item->package_set_quantity ?: 1,
            'superset' => $item->package_superset_quantity ?: 1,
            'palette' => $item->package_palette_quantity ?: 1,
            'container' => $item->package_container_quantity ?: 1,
        ];

        $data = [
            'unitQty' => $quantity,
            'setQty' => 0,
            'supersetQty' => 0,
            'paletteQty' => 0,
            'containerQty' => 0,
            'unitName' => $item->package_unit_name,
            'setName' => $item->package_set_name,
            'supersetName' => $item->package_superset_name,
            'paletteName' => $item->package_palette_name,
            'containerName' => $item->package_container_name,
        ];

        $remainingQty = $quantity;

        if ($packageId >= 2) {
            $data['setQty'] = intdiv($remainingQty, $multipliers['set']);
            $remainingQty %= $multipliers['set'];
        }
        $data['unitQty'] = $remainingQty;

        if ($packageId >= 3) {
            $data['supersetQty'] = intdiv($data['setQty'], $multipliers['superset']);
            $data['setQty'] %= $multipliers['superset'];
        }
        if ($packageId >= 4) {
            $data['paletteQty'] = intdiv($data['supersetQty'], $multipliers['palette']);
            $data['supersetQty'] %= $multipliers['palette'];
        }
        if ($packageId == 5) {
            $data['containerQty'] = intdiv($data['paletteQty'], $multipliers['container']);
            $data['paletteQty'] %= $multipliers['container'];
        }

        return $data;
    }

    /**
     * Get package ID by name
     */
    public static function getPackageIdByName(string $packageName, Item $item): ?int
    {
        $name = trim($packageName);

        $mapping = [
            1 => $item->package_unit_name,
            2 => $item->package_set_name,
            3 => $item->package_superset_name,
            4 => $item->package_palette_name,
            5 => $item->package_container_name,
        ];

        foreach ($mapping as $id => $pkgName) {
            if ($name === $pkgName) return $id;
        }

        return null;
    }

    /**
     * Generate auto item main code
     */
    public static function generateMainCode(int $companyId): string
    {
        $latestItem = Item::where('company_id', $companyId)
            ->where('auto_generated_code', true)
            ->whereNotNull('main_code')
            ->latest()
            ->first();

        $newNumber = 1;

        if ($latestItem) {
            $lastNumber = (int) str_replace(ItemConstants::NUMBER_PREFIX, '', $latestItem->main_code);

            $newNumber = $lastNumber + 1;
        }

        $paddedNumber = str_pad(
            $newNumber,
            max(strlen((string)$newNumber), ItemConstants::NUMBER_MIN_LENGTH),
            ItemConstants::NUMBER_PAD_STR,
            STR_PAD_LEFT
        );

        return ItemConstants::NUMBER_PREFIX . $paddedNumber;
    }

    public static function syncItemCodesAndGroups($item, array $itemCodes = [], array $itemGroups = [])
    {
        $barcodes = [];
        $alternativeCodes = [];
        $supplierCodes = [];

        foreach ($itemCodes as $itemCode) {
            switch ($itemCode['type']) {
                case 'barcode':
                    $barcodes[] = $itemCode;
                    break;
                case 'alternative':
                    $alternativeCodes[] = $itemCode;
                    break;
                case 'supplier':
                    $supplierCodes[] = $itemCode;
                    break;
            }
        }

        // إنشاء Barcodes
        foreach ($barcodes as $barcode) {
            BarCode::create([
                'company_id' => $item->company_id,
                'code' => $barcode['code'],
                'print_code' => $barcode['print'],
                'item_id' => $item->id,
            ]);
        }

        // إنشاء SupplierCodes
        foreach ($supplierCodes as $supplierCode) {
            SupplierCode::create([
                'company_id' => $item->company_id,
                'code' => $supplierCode['code'],
                'print_code' => $supplierCode['print'] ,
                'item_id' => $item->id,
            ]);
        }

        // إنشاء AlternativeCodes
        foreach ($alternativeCodes as $alternativeCode) {
            AlternativeCode::create([
                'company_id' => $item->company_id,
                'code' => $alternativeCode['code'],
                'print_code' => $alternativeCode['print'] ,
                'item_id' => $item->id,
            ]);
        }

        // sync item groups
        if (!empty($itemGroups)) {
            $item->itemGroups()->syncWithoutDetaching($itemGroups);
        }
    }
    public function getAvailableStock(int $warehouseId, int $itemId): float
    {
        return StockMovement::where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->sum('quantity');
    }
}
