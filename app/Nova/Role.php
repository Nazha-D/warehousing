<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\MultiselectField\Multiselect;
use Spatie\Permission\Models\Permission;
class Role extends Resource
{
    public static $model = \App\Models\Role::class;
    public static $displayInNavigation = true;
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')->sortable()->rules('required'),

            Text::make('Guard Name')->sortable()
                ->default('web')->readonly() ,
            Select::make('Company', 'company_id')
                ->options(
                    \App\Models\Company::orderBy('name')->pluck('name', 'id')->toArray()
                )
                ->displayUsingLabels()
                ->sortable()
                ->rules('required')
                ->searchable(),
            Multiselect::make('Permissions')
                ->options(Permission::pluck('name', 'name')->toArray())
                ->saveAsJSON(false)
                ->fillUsing(function (NovaRequest $request, $model, $attribute, $requestAttribute) {
                    if ($request->exists($requestAttribute)) {
                        $permissions = $request->input($requestAttribute);
                        $model->syncPermissions($permissions);
                        //$model->syncPermissions($permissions);
                    }
                })
                ->withMeta([
                    'value' => $this->permissions->pluck('name')->toArray(), // هذه السطر المهم
                ])
                ->hideFromIndex()   // يخفيه من جدول الـ Index
                ->hideFromDetail(),

            Text::make('Permissions', function () {
                return collect($this->permissions)->map(function($p){
                    return '<span style="display:inline-block;padding:2px 6px;margin:2px;background-color:#f0f0f0;border-radius:4px;">'.$p->name.'</span>';
                })->implode(' ');
            })->asHtml()->onlyOnIndex(),

            // عرض Permissions كـ Badges في Detail
            Text::make('Permissions', function () {
                return collect($this->permissions)->map(function($p){
                    return '<span style="display:inline-block;padding:2px 6px;margin:2px;background-color:#aac6e3;border-radius:4px;">'.$p->name.'</span>';
                })->implode(' ');
            })->asHtml()->onlyOnDetail(),
            BelongsToMany::make('Users','users', \App\Nova\User::class),
        ];
    }

    public static function authorizedToViewAny(Request $request)
    {
        return true; // لتسهيل التجربة
    }

}
