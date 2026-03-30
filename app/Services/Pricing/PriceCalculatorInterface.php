<?php
namespace App\Services\Pricing;

use App\Models\Item;
use App\Models\Client;
use App\Models\PriceList;
use App\DTO\ResolvedPrice;

interface PriceCalculatorInterface
{
    public function calculate(
        Item $item,
        Client $client,
        ?PriceList $priceList = null
    ): ResolvedPrice;
}
