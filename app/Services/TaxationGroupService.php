<?php

namespace App\Services;

use App\Models\TaxationGroup;

class TaxationGroupService
{
    public static function getAll($canViewAllCompanies, $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $onlyActive = false;
        $searchDefault = '';

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $onlyActive = json_decode($options['onlyActive'] ?? $onlyActive);
        $search = $options['search'] ?? $searchDefault;

        $taxationGroups = TaxationGroup::query();

        if (! $canViewAllCompanies) {
            $taxationGroups->where('company_id', $userCompanyId);
        }

        $taxationGroups->filter($search);

        if ($onlyActive) {
            $taxationGroups->active();
        }

        if ($isPaginated) {
            $taxationGroups = $taxationGroups->paginate($perPage);
        } else {
            $taxationGroups = $taxationGroups->get();
        }

        return $taxationGroups;
    }
}
