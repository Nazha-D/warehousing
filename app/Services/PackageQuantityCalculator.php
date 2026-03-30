<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Package;

class PackageQuantityCalculator
{
    /**
     * حساب الكمية الفعلية لأي منتج حسب اسم أو رقم الباكج
     *
     * @param Item $item
     * @param string|int $packageRef اسم الباكج أو رقمها
     * @param float $quantityRequested الكمية المطلوبة
     * @return float
     */
    public static function calculate(Item $item, $packageRef, float $quantityRequested): float
    {
        // ترتيب المستويات من الأصغر للأكبر مع الاسم والكمية
        $packageLevels = [
            ['name' => $item->package_unit_name,      'quantity' => $item->package_unit_quantity ?? 1],
            ['name' => $item->package_set_name,       'quantity' => $item->package_set_quantity ?? 1],
            ['name' => $item->package_superset_name,  'quantity' => $item->package_superset_quantity ?? 1],
            ['name' => $item->package_palette_name,   'quantity' => $item->package_palette_quantity ?? 1],
            ['name' => $item->package_container_name, 'quantity' => $item->package_container_quantity ?? 1],
        ];

        // إيجاد المستوى الذي اختاره المستخدم
        $startIndex = null;

        foreach ($packageLevels as $index => $level) {
            if (!$level['name']) continue; // تجاهل المستويات الفارغة

            // المطابقة مع الاسم أو الرقم
            if ($packageRef == $level['name'] || $packageRef == $index + 1) {
                $startIndex = $index;
                break;
            }
        }

        // إذا لم نجد أي مطابقة → نرجع الكمية كما هي
        if ($startIndex === null) {
            return $quantityRequested;
        }

        // نبدأ بالكمية المطلوبة ونضربها بالمستوى المختار وكل المستويات الأدنى منه
        $actualQty = $quantityRequested;

        for ($i = $startIndex; $i >= 0; $i--) {
            $actualQty *= $packageLevels[$i]['quantity'];
        }

        return $actualQty;
    }
    static function getPackageIdByName(string $packageName, int $itemId): ?int
    {
        $name = trim($packageName, '"');
        $item=Item::find($itemId);
        $mapping = [
            1 => $item->package_unit_name,
            2 => $item->package_set_name,
            3 => $item->package_superset_name,
            4 => $item->package_palette_name,
            5 => $item->package_container_name,
        ];

        foreach ($mapping as $id => $pkgName) {
            if ($name === $pkgName) {
                return $id;
            }
        }

        return null;
    }
    public static function getItemQuantitiesWithPackages($itemId, $quantity, $packageId)
    {
        $item = Item::findOrFail($itemId);
        $packageId = min($packageId, $item->package_id);

        $data = [
            'containerName' => $item->package_container_name,
            'containerQty' => 0,
            'paletteName' => $item->package_palette_name,
            'paletteQty' => 0,
            'supersetName' => $item->package_superset_name,
            'supersetQty' => 0,
            'setName' => $item->package_set_name,
            'setQty' => 0,
            'unitName' => $item->package_unit_name,
            'unitQty' => $quantity,
        ];

        $setQty = $item->package_set_quantity ?: 1;
        $supersetQty = $item->package_superset_quantity ?: 1;
        $paletteQty = $item->package_palette_quantity ?: 1;
        $containerQty = $item->package_container_quantity ?: 1;

        if ($packageId >= 2) {
            $data['setQty'] = intdiv($quantity, $setQty);
            $data['unitQty'] = $quantity % $setQty;
        }

        if ($packageId >= 3) {
            $data['supersetQty'] = intdiv($data['setQty'], $supersetQty);
            $data['setQty'] %= $supersetQty;
        }

        if ($packageId >= 4) {
            $data['paletteQty'] = intdiv($data['supersetQty'], $paletteQty);
            $data['supersetQty'] %= $paletteQty;
        }

        if ($packageId == 5) {
            $data['containerQty'] = intdiv($data['paletteQty'], $containerQty);
            $data['paletteQty'] %= $containerQty;
        }

        return $data;
    }

}
