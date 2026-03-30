<?php

namespace App\Services;

use App\Models\PaymentTerm;

class PaymentTermService
{
    public static function getAll( $userCompanyId = null, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = false;
        $searchDefault='';
        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search=json_decode($options['search'] ?? $searchDefault);
        $paymentTerms = PaymentTerm::query();
        if($search)
        {
            $paymentTerms->filter($search);
        }

            $paymentTerms = $paymentTerms->where('company_id', $userCompanyId);
            if ($isPaginated) {
                $paymentTerms = $paymentTerms->paginate($perPage);
            } else {
                $paymentTerms = $paymentTerms->get();
            }


        return $paymentTerms;
    }
}
