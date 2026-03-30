<?php

namespace App\Services;

use App\Models\Category;


class CategoryService
{
    public static function getAll($canViewAllCategories, $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';
        $onlyActive = false;

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;
        $onlyActive = json_decode($options['onlyActive'] ?? $onlyActive);

        $categories = Category::query();

        if (!$canViewAllCategories) {
            $categories->with('children')->where('company_id', $userCompanyId);
        }

        $categories->filter($search);

//        if ($onlyActive) {
//            $categories->with('children')->active();
//        }

        if ($isPaginated) {
            $categories = $categories->with('children')->paginate($perPage);
        } else {
            $categories = $categories->with('children')->get();
        }

        return $categories;
    }

    public static function createCategoryWithChildren(array $data, ?Category $parent = null, int $companyId, &$createdGroups = [])
    {
        // Create the group
        $Category = new Category([
            'category_name' => $data['name'],

            'active' => 1,
            'company_id' => $companyId,
        ]);

        // Attach to parent if exists
        if ($parent) {
            $Category->appendToNode($parent);
        }

        $Category->save();
        $createdGroups[] = $Category;
        // If this group has children, handle them recursively
        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $childData) {
                self::createCategoryWithChildren($childData, $Category, $companyId,$createdGroups);
            }
        }

        return $createdGroups;
    }


}
