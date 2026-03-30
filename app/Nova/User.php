<?php

namespace App\Nova;

use App\Nova\Filters\ActiveStatus;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\MultiselectField\Multiselect;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Gravatar::make()->maxWidth(50),
            BelongsTo::make('Company', 'company', \App\Nova\Company::class)
                ->sortable()
                ->searchable()
                ->nullable(),
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),



//            Text::make('Roles', function () {
//                return collect($this->roles)->map(function($p){
//                    return '<span style="display:inline-block;padding:2px 6px;margin:2px;background-color:#f0f0f0;border-radius:4px;">'.$p->name.'</span>';
//                })->implode(' ');
//            })->asHtml()->onlyOnIndex(),
            Multiselect::make('Roles')
                ->dependsOn(
                    ['company'],
                    function (Multiselect $field, NovaRequest $request, $formData) {

                        if (! $formData->company) {
                            $field->options([]);
                            return;
                        }

                        $field->options(
                           \App\Models\Role::where('company_id', $formData->company)
                                ->pluck('name', 'name') // name لأنك تستخدمين syncRoles
                                ->toArray()
                        );
                    }
                )
                ->saveAsJSON(false)
                ->fillUsing(function (NovaRequest $request, $model, $attribute, $requestAttribute) {
                    if ($request->exists($requestAttribute)) {
                        $roles = $request->input($requestAttribute);
                        app(PermissionRegistrar::class)
                            ->setPermissionsTeamId($request->company);

                        $model->syncRoles($roles);
                    }
                })
                ->withMeta([
                    'value' => $this->roles->pluck('name')->toArray(),
                ])
                ->hideFromIndex()
                ->hideFromDetail()
                ->help('Roles are filtered based on the selected company.'),


            // عرض Permissions كـ Badges في Detail
            Text::make('Roles', function () {
                return collect($this->roles)->map(function($p){
                    return '<span style="display:inline-block;padding:2px 6px;margin:2px;background-color:#aac6e3;border-radius:4px;">'.$p->name.'</span>';
                })->implode(' ');
            })->asHtml()->onlyOnDetail(),
        ];


    }
    public static function authorizedToViewAny(Request $request)
    {
        return true;
    }
    public static function authorizedToCreate(Request $request)
    {
        return true;
    }
    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            new ActiveStatus(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
