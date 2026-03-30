<?php

namespace App\Services;

use App\Models\ItemGroup;

class ItemGroupService
{
    public static function getAll($canViewAllCompanies, $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = false;
        $onlyActive = false;
        $searchDefault = '';

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $onlyActive = json_decode($options['onlyActive'] ?? $onlyActive);
        $search = $options['search'] ?? $searchDefault;

        // Start query
        $itemGroups = ItemGroup::query()->with('childrenRecursive');
// $itemGroups->with('childrenRecursive');
        // Filter by company if necessary
        if (!$canViewAllCompanies) {
            $itemGroups->where('company_id', $userCompanyId);
        }

        // Apply search filter if provided
        $itemGroups->filter($search);

        // Apply active filter if necessary
        if ($onlyActive) {
            $itemGroups->active();
        }

        // Recursively load children


        // Handle pagination
        if ($isPaginated) {
            $itemGroups = $itemGroups->paginate($perPage);
        } else {
            $itemGroups = $itemGroups->get();
        }

        return $itemGroups;
    }

    public static function createGroupWithChildren(array $data, ?ItemGroup $parent = null, int $companyId, &$createdGroups = [])
    {
        // Create the group
        $itemGroup = new ItemGroup([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'active' => 1,
            'company_id' => $companyId,
        ]);

        // Attach to parent if exists
        if ($parent) {
            $itemGroup->appendToNode($parent);
        }

        $itemGroup->save();
        $createdGroups[] = $itemGroup;
        // If this group has children, handle them recursively
        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $childData) {
                self::createGroupWithChildren($childData, $itemGroup, $companyId,$createdGroups);
            }
        }

        return $createdGroups;
    }


}
