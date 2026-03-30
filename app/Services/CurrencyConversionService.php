<?php

namespace App\Services;

use App\Repositories\CurrencyRateRepositoryInterface;
use App\Repositories\CurrencyRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CurrencyConversionService
{
    protected $repository;
    protected $apiService;
    protected $currencyRepository;
    public function __construct(
        CurrencyRateRepositoryInterface $repository,
        CurrencyRepositoryInterface $currencyRepository,
        ExternalCurrencyApiService $apiService
    ) {
        $this->repository = $repository;
        $this->currencyRepository = $currencyRepository;
        $this->apiService = $apiService;
    }

    /**
     * Convert amount between currencies with fallback logic:
     * 1. Manual rate (company)
     * 2. Auto rate (database)
     * 3. API (external)
     *
     * @param int|null $companyId
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @param float $amount
     * @return float
     * @throws \Exception
     */
//    public function convert(int $companyId = null, int $fromCurrencyId, int $toCurrencyId, float $amount): float
//    {
//        // 1. Get rate from repository (manual or auto)
//        $rateRecord = $this->repository->getRate($companyId, $fromCurrencyId, $toCurrencyId);
//
//        if ($rateRecord) {
//            return $amount * $rateRecord->rate;
//        }
//
//        // 2. Fallback: use external API
//        $fromCurrency = $this->currencyRepository->getCodeById($fromCurrencyId);
//        $toCurrency = $this->currencyRepository->getCodeById($toCurrencyId);
//
//        $apiRate = $this->apiService->getUsdRate( $toCurrency);
//
//        if (!$apiRate) {
//            throw new \Exception("Unable to fetch exchange rate from API.");
//        }
//
//        // 3. Save as AUTO rate in repository
//        $this->repository->saveAutoRates([
//            [
//                'from_currency_id' => $fromCurrencyId,
//                'to_currency_id' => $toCurrencyId,
//                'rate' => $apiRate,
//                'source_type' => 'AUTO',
//            ]
//        ]);
//
//        return $amount * $apiRate;
//    }
    public function convert(
        ?int $companyId,
        int $fromId,
        int $toId,
        float $amount
    ): float {
        $rate = $this->getRate($companyId, $fromId, $toId);

        return $amount * $rate;
    }
    public function getRate(?int $companyId, int $fromId, int $toId): float
    {
        if ($fromId === $toId) {
            return 1.0;
        }

        // 1️⃣ Company Manual مباشر
        if ($companyId) {
            $manual = $this->repository
                ->getCompanyManualRate($companyId, $fromId, $toId);

            if ($manual) {
                return (float) $manual->rate;
            }
        }

        // 2️⃣ Global AUTO مباشر
        $auto = $this->repository
            ->getGlobalAutoRate($fromId, $toId);

        if ($auto) {
            return (float) $auto->rate;
        }

        // 3️⃣ Company Manual عكسي
        if ($companyId) {
            $reverseManual = $this->repository
                ->getCompanyManualRate($companyId, $toId, $fromId);

            if ($reverseManual) {
                return 1 / (float) $reverseManual->rate;
            }
        }

        // 4️⃣ Global AUTO عكسي
        $reverseAuto = $this->repository
            ->getGlobalAutoRate($toId, $fromId);

        if ($reverseAuto) {
            return 1 / (float) $reverseAuto->rate;
        }

        // 5️⃣ API fallback
        return $this->resolveViaUsdApi($fromId, $toId);
    }
    protected function resolveViaUsdApi(int $fromId, int $toId): float
    {
        $fromCode = $this->currencyRepository->getCodeById($fromId);
        $toCode   = $this->currencyRepository->getCodeById($toId);

        if (!$fromCode || !$toCode) {
            throw new \Exception("Invalid currency IDs.");
        }

        $quotes = $this->apiService->getAllUsdRates();
        $usdId  = config('currency.usd_id');

        $usdToFrom = $quotes['USD' . $fromCode] ?? null;
        $usdToTo   = $quotes['USD' . $toCode] ?? null;

        if (!$usdToFrom || !$usdToTo) {
            throw new \Exception("No exchange rate available for {$fromCode} → {$toCode}.");
        }

        // خزّن USD rates فقط
        $this->repository->saveAutoRates([
            [
                'company_id' => null,
                'from_currency_id' => $usdId,
                'to_currency_id' => $fromId,
                'rate' => $usdToFrom,
                'source_type' => 'AUTO',
            ],
            [
                'company_id' => null,
                'from_currency_id' => $usdId,
                'to_currency_id' => $toId,
                'rate' => $usdToTo,
                'source_type' => 'AUTO',
            ],
        ]);

        return (float) ($usdToTo / $usdToFrom);
    }
    public function getReverseRate(
        ?int $companyId,
        int $fromId,
        int $toId
    ) {
        $query = CurrencyRate::where('from_currency_id', $fromId)
            ->where('to_currency_id', $toId);

        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->where(function ($sub) use ($companyId) {
                    $sub->where('company_id', $companyId)
                        ->where('source_type', 'MANUAL');
                })->orWhere(function ($sub) {
                    $sub->whereNull('company_id')
                        ->where('source_type', 'AUTO');
                });
            });
        } else {
            $query->whereNull('company_id')
                ->where('source_type', 'AUTO');
        }

        return $query->latest('updated_at')->first();
    }
}
