<?php

namespace App\Nova\Actions;

use App\Models\Currency;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\MultiselectField\Multiselect;

class AssignCurrenciesToCompany extends Action
{
    use InteractsWithQueue, Queueable;
    public $name = 'Assign Currencies';
    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {

        foreach ($models as $company) {

            $company->currencies()->sync($fields->currencies);
        }

        return Action::message('Currencies assigned successfully.');
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [ MultiSelect::make('Currencies')
            ->options(
                Currency::all()->pluck('name', 'id')
            )
            ->rules('required')];
    }
}
