<?php

namespace App\Repositories;

use App\Models\Currency;
use App\Models\ExchangeRate;

class CurrencyRateRepository implements CurrencyRateRepositoryInterface
{
//    public function getRate(int $companyId = null, string $from, string $to)
//    {
//        return ExchangeRate::query()
//            ->where('from_currency_id', $from)
//            ->where('to_currency_id', $to)
//            ->where(function ($q) use ($companyId) {
//                $q->whereNull('company_id')
//                    ->orWhere('company_id', $companyId);
//            })
//            ->orderByRaw("company_id IS NULL") // يعطي الأولوية لسعر الشركة
//            ->first();
//    }
//
    public function upsertManualRate(int $companyId, string $from, string $to, float $rate, int $userId)
    {
        return ExchangeRate::updateOrCreate(
            [
                'company_id' => $companyId,
                'from_currency_id' => $from,
                'to_currency_id' => $to,
            ],
            [
                'rate' => $rate,
                'source_type' => 'MANUAL',
                'updated_by_user_id' => $userId,
            ]
        );
    }
    public function getRate($companyId, $from, $to)
    {
        // 1) Direct rate
        $direct = ExchangeRate::where('from_currency_id', $from)
            ->where('to_currency_id', $to)
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->orWhereNull('company_id');
            })
            ->orderByRaw('company_id IS NULL') // manual/company أولاً
            ->first();

        if ($direct) {
            return $direct;
        }

        // 2) Cross rate via base currency (USD)
        $baseId = Currency::where('code', config('exchangerate.base'))->value('id');

        $fromBase = ExchangeRate::where('from_currency_id', $baseId)
            ->where('to_currency_id', $from)
            ->first();

        $toBase = ExchangeRate::where('from_currency_id', $baseId)
            ->where('to_currency_id', $to)
            ->first();

        if ($fromBase && $toBase) {
            $crossRate = $toBase->rate / $fromBase->rate;

            return (object)[
                'rate' => $crossRate
            ];
        }

        return null;
    }

    public function saveAutoRates(array $rates)
    {
        foreach ($rates as $row) {
            ExchangeRate::updateOrCreate(
                [
                    'company_id' => $row['company_id'] ?? null,
                    'from_currency_id' => $row['from_currency_id'],
                    'to_currency_id' => $row['to_currency_id'],
                ],
                [
                    'rate' => $row['rate'],
                    'source_type' => $row['source_type'] ?? 'AUTO',
                    'updated_by_user_id' => $row['updated_by_user_id'] ?? null,
                ]
            );
        }
    }
    public function getCompanyManualRate(
        int $companyId,
        int $fromId,
        int $toId
    ) {
        return ExchangeRate::where('company_id', $companyId)
            ->where('from_currency_id', $fromId)
            ->where('to_currency_id', $toId)
            ->where('source_type', 'MANUAL')
            ->latest('updated_at')
            ->first();
    }
    public function getGlobalAutoRate(
        int $fromId,
        int $toId
    ) {
        return ExchangeRate::whereNull('company_id')
            ->where('from_currency_id', $fromId)
            ->where('to_currency_id', $toId)
            ->where('source_type', 'AUTO')
            ->latest('updated_at')
            ->first();
    }
}
