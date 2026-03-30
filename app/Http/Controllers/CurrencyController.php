<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Services\CurrencyConversionService;
use App\Services\CurrencyService;
use App\Enums\ExchangRateModeEnum;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    use ApiResponseTrait;

    protected $service;

    public function __construct(CurrencyConversionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Currency::class);

        $options = [
            'isPaginated' => $request->query('isPaginated'),
            'perPage' => $request->query('perPage'),
            'search' => $request->query('search'),
        ];

        $currencies = CurrencyService::getAll($options);
        $message = 'Got currencies successfully';
        return $this->successResponse($currencies, $message, 200);
    }

    public function convert(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|integer',
            'from_currency_id' => 'required|integer|exists:currencies,id',
            'to_currency_id' => 'required|integer|exists:currencies,id',
            'amount' => 'required|numeric|min:0',
        ]);
//         $result=[];
        $result = $this->service->convert(
            $validated['company_id'] ?? null,
            $validated['from_currency_id'],
            $validated['to_currency_id'],
            $validated['amount']
        );

        return $this->successResponse($result, 'Got result successfully', 200);
    }

    public function updateManual(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();

            $validated = $request->validate([
                'from_currency_id' => 'required|integer|exists:currencies,id',
                'to_currency_id' => 'required|integer|exists:currencies,id',
                'rate' => 'required|numeric|min:0',
            ]);

            $existingRate = ExchangeRate::where([
                'company_id' => $user->company_id,
                'from_currency_id' => $validated['from_currency_id'],
                'to_currency_id' => $validated['to_currency_id'],
            ])->first();

            if ($existingRate) {
                $this->authorize('update', $existingRate);
            } else {
                $this->authorize('create', ExchangeRate::class);
            }

            $rate = ExchangeRate::updateOrCreate(
                [
                    'company_id' => $user->company_id,
                    'from_currency_id' => $validated['from_currency_id'],
                    'to_currency_id' => $validated['to_currency_id'],
                ],
                [
                    'rate' => $validated['rate'],
                    'source_type' => 'MANUAL',
                    'updated_by_user_id' => $user->id,
                ]
            );

            DB::commit();
            return $this->successResponse($rate, 'Manual exchange rate updated successfully', 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->errorResponse($exception->getMessage(), 500, []);
        }
    }

    public function listCompanyRates($companyId)
    {
        // Manual rates for the company
        $manual = ExchangeRate::where('company_id', $companyId)
            ->orderBy('from_currency_id')
            ->orderBy('to_currency_id')
            ->get();

        // AUTO rates (general)
        $auto = ExchangeRate::whereNull('company_id')
            ->where('source_type', 'AUTO')
            ->orderBy('from_currency_id')
            ->orderBy('to_currency_id')
            ->get();

        $data = [
            'manual_rates' => $manual,
            'auto_rates' => $auto,
        ];

        return $this->successResponse($data, 'Exchange rates fetched successfully', 200);
    }

    public  function deleteManual(ExchangeRate $exchangeRate)
    {
        try{


            $this->authorize('delete',$exchangeRate);
            DB::beginTransaction();
             if($exchangeRate->source_type===\App\Enums\ExchangeRateModeEnum::AUTOMATIC->value)
            {
                return $this->errorResponse('Not Allowed to delete an Automatically generated Exchange Rate', 406);
            }
             if($exchangeRate->company_id!==auth()->user()->company_id) {
                return $this->errorResponse('Not Allowed to delete this Exchange Rate', 406);
            }
            $exchangeRate->delete();
            DB::commit();
            return  $this->successResponse([],'Exchange rate deleted successfully');
        }
        catch(\Exception $exception){
            DB::rollBack();
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
    public function getRate(Request $request)
    {
        try {
            $request->validate([
                'from_currency_id' => 'required|integer',
                'to_currency_id' => 'required|integer',
            ]);

            $rate = $this->service->getRate(
                auth()->user()->company_id ?? null,
                $request->from_currency_id,
                $request->to_currency_id
            );

            return $this->successResponse($rate, 'Got rate Successfully', 200);
        }
        catch(\Exception $exception){
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
}
