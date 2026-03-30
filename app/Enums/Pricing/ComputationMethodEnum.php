<?php
namespace App\Enums\Pricing;

enum ComputationMethodEnum: string
{
case PERCENTAGE    = 'percentage';
case FIXED_AMOUNT  = 'fixed_amount';
case FIXED_PRICE   = 'fixed_price';
}
