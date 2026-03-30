<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class Permission extends Resource
{
    public static $model = \Spatie\Permission\Models\Permission::class;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')->sortable()->rules('required'),

            Text::make('Guard Name')->sortable(),

            BelongsToMany::make('Roles'),
            BelongsToMany::make('Users'),
        ];
    }
}
