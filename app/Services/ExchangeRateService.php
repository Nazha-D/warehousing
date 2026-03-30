<?php

namespace App\Services;

use App\Models\ExchangeRate;

class ExchangeRateService
{
    /**
     * Update AUTO exchange rates coming from API
     *
     * @param array $rates [
     *      [
     *          'from_currency_id' => int,
     *          'to_currency_id'   => int,
     *          'rate'             => float,
     *      ],
     *      ...
     * ]
     */
    public function updateRatesFromAPI(array $rates): bool
    {
        foreach ($rates as $rateItem) {
            ExchangeRate::updateOrCreate(
                [
                    'company_id'       => null, // generic rate
                    'from_currency_id' => $rateItem['from_currency_id'],
                    'to_currency_id'   => $rateItem['to_currency_id'],
                ],
                [
                    'rate'              => $rateItem['rate'],
                    'source_type'       => 'AUTO',
                    'updated_by_user_id'=> null,
                ]
            );
        }

        return true;
    }


    /**
     * Get exchange rate (manual for company OR fallback to AUTO)
     *
     * Priority:
     *   1. MANUAL rate for the company
     *   2. AUTO rate (global)
     *
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @param int|null $companyId
     * @return float|null
     */
    public function getRate(int $fromCurrencyId, int $toCurrencyId, ?int $companyId = null): ?float
    {
        // Try manual first if companyId provided
        if ($companyId) {
            $manualRate = ExchangeRate::where([
                'company_id'       => $companyId,
                'from_currency_id' => $fromCurrencyId,
                'to_currency_id'   => $toCurrencyId,
                'source_type'      => 'MANUAL',
            ])->first();

            if ($manualRate) {
                return $manualRate->rate;
            }
        }

        // Otherwise fall back to AUTO rate
        $autoRate = ExchangeRate::whereNull('company_id')
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->where('source_type', 'AUTO')
            ->first();

        return $autoRate?->rate;
    }


    public function upsertManualRate(
        int $companyId,
        int $fromCurrencyId,
        int $toCurrencyId,
        float $rate,
        int $userId
    ): ExchangeRate {
        return ExchangeRate::updateOrCreate(
            [
                'company_id'       => $companyId,
                'from_currency_id' => $fromCurrencyId,
                'to_currency_id'   => $toCurrencyId,
            ],
            [
                'rate'              => $rate,
                'source_type'       => 'MANUAL',
                'updated_by_user_id'=> $userId,
            ]
        );
    }

}
