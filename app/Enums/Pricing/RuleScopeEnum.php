<?php
namespace App\Enums\Pricing;

enum RuleScopeEnum: string
{
case GLOBAL   = 'global';
case CATEGORY = 'category';
case ITEM = 'item';
}
