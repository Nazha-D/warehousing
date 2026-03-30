<?php
namespace App\Http\Controllers;
use App\Http\Requests\CurrencyRequests\StoreCompanyCurrenciesRequest;
use App\Http\Requests\CurrencyRequests\StoreManualExchangeRateRequest;
use App\Models\Currency;
use App\Services\CompanyCurrencyService;
use App\Services\ExchangeRateService;
use App\Traits\ApiResponseTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyCurrencyController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        CompanyCurrencyService $currencyService,
        ExchangeRateService $rateService
    ) {}

    public function listCompanyCurrencies(Request $request)
    {
        try {
            $companyId = auth()->user()->company_id;

            $currencies = Currency::query()->with([
                'outgoingRates',
                'incomingRates',
            ])
                ->whereHas('companies', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->get();
       if( $request->query('default'))
                 $currencies = Currency::query()->with([
                     'outgoingRates',
                     'incomingRates',
                 ])
                     ->whereHas('companies', function ($q) use ($companyId) {
                         $q->where('company_id', $companyId)->where('is_default','=',1);
                     })
                     ->get();
            return $this->successResponse($currencies, 'Company currencies retrieved Successfully');

        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function storeCompanyCurrencies(StoreCompanyCurrenciesRequest $request)
    {
        try {
            $user = auth()->user();

            app(CompanyCurrencyService::class)
                ->store($user->company_id, $request->currency_ids);

            return $this->successResponse([], 'Company currencies saved');

        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function storeManualRate(StoreManualExchangeRateRequest $request)
    {
        try {
            $user = auth()->user();
            $company = $user->company;

            if ($company->exchange_rate_mode === 'AUTO') {
                abort(403, 'Company is not in MANUAL exchange rate mode');
            }
            $exists = DB::table('company_currencies')
                    ->where('company_id', $company->id)
                    ->whereIn('currency_id', [
                        $request->from_currency_id,
                        $request->to_currency_id
                    ])
                    ->count() === 2;

            if (! $exists) {
                abort(422, 'Currency not selected for this company');
            }

                $rate = app(ExchangeRateService::class)
                    ->upsertManualRate(
                        $company->id,
                        $request->from_currency_id,
                        $request->to_currency_id,
                        $request->rate,
                        $user->id
                    );

            return $this->successResponse($rate, 'Exchange rate saved');

        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

}
