<?php

namespace App\Nova;
use App\Enums\ExchangeRateModeEnum;
use App\Nova\Actions\AssignCurrenciesToCompany;
use App\Nova\Filters\ActiveStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\BelongsToMany;
use App\Models\Currency;
use Outl1ne\MultiselectField\Multiselect;

class Company extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Company>
     */
    public static $model = \App\Models\Company::class;

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
        'id',
        'name'
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
            Text::make('Name')->sortable()->rules('required'),
            Text::make('email')->sortable()->rules('email'),
            Text::make('address')->rules('nullable'),
            Select::make('Phone Code', 'phone_code')
                ->options(config('phone_codes'))
                ->displayUsingLabels()
                ->searchable()
                ->sortable(),
            Text::make('phone_number')->rules('nullable'),
            Boolean::make('Is Active', 'is_active')
                ->sortable()
                ->trueValue(1)
                ->falseValue(0)
                ->default(true),
            Select::make('Exchange Rate Mode', 'exchange_rate_mode')
                ->options([
                    \App\Enums\ExchangeRateModeEnum::MANUAL->value => 'MANUAL',
            \App\Enums\ExchangeRateModeEnum::AUTOMATIC->value => 'AUTO',
            ])
            ->displayUsingLabels()
        ->rules('required'),
        Multiselect::make('Currencies')
            ->options(
                \App\Models\Currency::pluck('name', 'id')->toArray()
            )
            ->saveAsJSON(false)
            ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                if ($request->exists($requestAttribute)) {
                    $model->currencies()->sync($request->input($requestAttribute));
                }
            })
            ->withMeta([
                'value' => $this->resource->currencies->pluck('id')->toArray(),
            ])
            ->hideWhenCreating()
            ->onlyOnForms()
            ->help('Currencies can be assigned after the company is created.'),

    ];
}

// أضف هذا تحت fields() مباشرة داخل Resource
    public static function afterSave(NovaRequest $request, $model)
    {
        // 1️⃣ جيب ID عملة USD
        $usdId = \App\Models\Currency::where('code', 'USD')->value('id');

        if ($usdId) {
            // 2️⃣ أضف USD تلقائي للشركة إذا مش موجود
            $model->currencies()->syncWithoutDetaching([$usdId]);
        }
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
        return [
        ];
    }

}
