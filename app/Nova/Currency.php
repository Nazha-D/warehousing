<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class Currency extends Resource
{
    public static $model = \App\Models\Currency::class;

    public static $title = 'name';

    public static $search = [
        'id', 'code', 'name'
    ];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Code')->sortable(),
            Text::make('Name')->sortable(),
        ];
    }

    /**
     * منع أي Actions
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * منع التحديث، الإضافة، والحذف
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * إخفاء الأزرار على الفورم
     */
    public static $canRunValidationOnDetail = false;
}
