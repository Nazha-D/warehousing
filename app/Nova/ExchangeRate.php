<?php

namespace App\Nova;

use App\Models\Currency;
use App\Models\Company;
use App\Models\ExchangeRate as ExchangeRateModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\MultiselectField\Multiselect;

class ExchangeRate extends Resource
{
    public static $model = ExchangeRateModel::class;

    public static $title = 'id';

    public static $search = [
        'id',
    ];

    public function fields(NovaRequest $request)
    {
        // العملة الأساسية USD
        $usd = Currency::where('code', 'USD')->first();

        return [
            ID::make()->sortable(),

            BelongsTo::make('Company')
                ->sortable()
                ->searchable()
                ->rules('required'),

            // From Currency ثابت USD
            BelongsTo::make('From Currency', 'fromCurrency', \App\Nova\Currency::class)
                ->hideFromIndex()
                ->readonly()
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) use ($usd) {
                    // إذا لم يتم إدخال أي قيمة، نعطيه USD
                    $model->{$attribute} = $usd?->id;
    }),
            // To Currency يعتمد على الشركة
            Select::make('To Currency', 'to_currency_id')
                ->sortable()
                ->displayUsingLabels()
                ->dependsOn(
                    ['company'],
                    function ($field, NovaRequest $request, $formData) use ($usd) {
                        if (empty($formData->company)) {
                            $field->options([]);
                            return;
                        }

                        $company = Company::with('currencies')->find($formData->company);
                        if (!$company) {
                            $field->options([]);
                            return;
                        }

                        $field->options(
                            $company->currencies
                                ->where('id', '!=', $usd?->id)
                            ->pluck('name', 'id')
                            ->toArray()
                        );
                    }
                )
                ->rules('required'),

            // Rate
            Number::make('Rate')
                ->rules('required', 'numeric', 'min:0')
                ->step(0.0001)
                ->sortable(),

            // Source Type (manual)
            Text::make('Source Type', 'source_type')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->default('manual'),
        ];
    }

    public function cards(NovaRequest $request)
    {
        return [];
    }

    public function filters(NovaRequest $request)
    {
        return [];
    }

    public function lenses(NovaRequest $request)
    {
        return [];
    }

    public function actions(NovaRequest $request)
    {
        return [];
    }
}
