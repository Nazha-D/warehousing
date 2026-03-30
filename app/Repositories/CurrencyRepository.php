<?php

namespace App\Repositories;

use App\Models\Currency;
use App\Repositories\CurrencyRepositoryInterface;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    public function getCodeById(int $id): string
    {
        $currency = Currency::find($id);

        if (!$currency) {
            throw new \Exception("Currency not found: {$id}");
        }

        return $currency->code;
    }

}
