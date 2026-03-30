<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Pricing\RuleScopeEnum;
use App\Enums\Pricing\BaseSourceEnum;

class PriceListRule extends Model
{
    protected $fillable = [
        'price_list_id',
        'apply_on', // RuleScopeEnum
        'item_id', // nullable
        'category_id', // nullable
        'base_source', // PriceBaseSourceEnum
        'computation_method',
        'value',
        'priority',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'apply_on' => RuleScopeEnum::class,
        'base_source' => BaseSourceEnum::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // علاقات محتملة
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
