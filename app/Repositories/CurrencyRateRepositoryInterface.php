<?php

namespace App\Repositories;

interface CurrencyRateRepositoryInterface
{
    public function getRate(int $companyId = null, string $from, string $to);

    public function upsertManualRate(int $companyId, string $from, string $to, float $rate, int $userId);

    public function saveAutoRates(array $rates);
}
