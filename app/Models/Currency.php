<?php

namespace App\Models;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCurrency
 */
class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'active',
    ];

    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('name', 'like', '%' . $filter . '%')
                         ->orWhere('code', 'like', '%' . $filter . '%')
                ;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class,'company_currencies');
    }
//
//    public function items()
//    {
//        return $this->hasMany(Item::class,'currency_id');
//    }
//    public function itemsWithPriceCurrency()
//    {
//        return $this->hasMany(Item::class,'price_currency_id');
//    }
//    public function itemsWithPosCurrency()
//    {
//        return $this->hasMany(Item::class,'pos_currency_id');
//    }
//    public function replenishments()
//    {
//        return $this->hasMany(Replenishment::class,'replenishment_currency_id');
//    }
    public function outgoingRates()
    {
        // rates where this currency is the source
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    public function incomingRates()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

}
