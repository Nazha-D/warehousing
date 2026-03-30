<?php

namespace App\Services;

use App\Constants\ComboConstants;
use App\Models\Combo;

class ComboService
{
    public static function getAll($canViewAllCompanies, $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';
        $onlyActive = false;

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;
        $onlyActive = json_decode($options['onlyActive'] ?? $onlyActive);

        $combos = Combo::query();

        if (!$canViewAllCompanies) {
            $combos->where('company_id', $userCompanyId);
        }

        $combos->filter($search);

        if ($onlyActive) {
            $combos->active();
        }

        if ($isPaginated) {
            $combos = $combos->paginate($perPage);
        } else {
            $combos = $combos->get();
        }

        return $combos;
    }

    public static function generateComboCode($companyId)
    {

        $currentYear = date('y');
        $latestCombo = Combo::where('company_id', $companyId)->withTrashed()->whereNotNull('code')->latest()->first();

        if ($latestCombo) {
            if (str_contains($latestCombo->code, ComboConstants::NUMBER_SEPARATOR)) {
                $lastNumber = (int)substr($latestCombo->code, 4);
            } else {
                $lastNumber = (int)substr($latestCombo->code, 3);
            }

            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return ComboConstants::NUMBER_PREFIX . str_pad($newNumber, ComboConstants::NUMBER_MIN_LENGTH, ComboConstants::NUMBER_PAD_STR, STR_PAD_LEFT);

    }
}
