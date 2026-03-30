<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Constants\WarehouseConstants;

class WarehouseService
{
    public static function getAll( $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginatedDefault = true;
        $nameDefault = '';

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginatedDefault);
        $name = $options['search'] ?? $nameDefault;

        $warehouses = Warehouse::active()->withoutTrashed();


            $warehouses->where('company_id', $userCompanyId)
                ->filter($name);


        return $isPaginated
            ? $warehouses->paginate($perPage)
            : $warehouses->get();
    }
    public static function generateWarehouseNumber(int $companyId): string
    {
        $prefixLength = strlen(WarehouseConstants::NUMBER_PREFIX);

        $latestWarehouse = Warehouse::where('company_id', $companyId)
            ->whereNotNull('warehouse_number')
            ->withTrashed()
            ->selectRaw(
                "MAX(CAST(SUBSTRING(warehouse_number, ? + 1) AS UNSIGNED)) as max_number",
                [$prefixLength]
            )
            ->first();

        $newNumber = $latestWarehouse?->max_number
        ? $latestWarehouse->max_number + 1
        : 1;

    return WarehouseConstants::NUMBER_PREFIX
        . str_pad(
            $newNumber,
            WarehouseConstants::NUMBER_MIN_LENGTH,
            WarehouseConstants::NUMBER_PAD_STR,
            STR_PAD_LEFT
        );
}

}
