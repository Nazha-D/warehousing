<?php

namespace App\Services;

use App\Models\Currency;

class CurrencyService
{
    public static function getAll( $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $nameDefault = '';

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $name = $options['search'] ?? $nameDefault;

        $currencies = Currency::with('outgoingRates','incomingRates')->active();

            $currencies = $currencies->filter($name);
            if ($isPaginated) {
                $currencies = $currencies->paginate($perPage);
            } else {
                $currencies = $currencies->get();
            }


        return $currencies;
    }
}
