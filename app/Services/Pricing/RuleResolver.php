<?php
namespace App\Services\Pricing;

use App\Models\Item;
use App\Models\PriceList;
use App\Enums\Pricing\RuleScopeEnum;
final class RuleResolver
{
    /**
     * جلب القواعد المطبقة على Item و PriceList، Parent → Child
     */
    public static function getApplicableRules(Item $item, PriceList $priceList)
    {
        return $priceList->rules
            ->filter(function ($rule) use ($item) {

                if ($rule->apply_on === RuleScopeEnum::GLOBAL) {
                    return true;
                }
                if ($rule->apply_on === RuleScopeEnum::ITEM) {
                    return $rule->item_id === $item->id;
                }

                if ($rule->apply_on === RuleScopeEnum::CATEGORY) {
                    return $rule->category_id === $item->category_id;
                }

                return false;
            })
            ->sortByDesc('priority')
            ->values();
    }
}
