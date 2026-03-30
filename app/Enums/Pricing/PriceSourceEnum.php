<?php
namespace App\Enums\Pricing;

enum PriceSourceEnum: string
{
case BASE_PRICE = 'base_price';        // السعر الأساسي للـ Item
case PRICE_LIST = 'price_list';        // ناتج عن Pricelist
case PARENT_PRICE_LIST = 'parent';     // ناتج عن Pricelist مشتقة
case MANUAL_OVERRIDE = 'manual';        // سعر يدوي
case FALLBACK = 'fallback';             // سعر احتياطي
}
