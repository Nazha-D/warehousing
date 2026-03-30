<?php

namespace App\Models;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCashingMethod
 */
class CashingMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'active',
        'company_id',
        'image'
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
  public function getImageAttribute($value)
  {
      if(!$value)
          return null;
      return  env('APP_URL').'storage/app/public/'.$value;
  }

//      public function orders()
//    {
//        return $this->belongsToMany(Order::class)->withPivot('order_id','cashing_method_id','usd_amount','other_currency_amount');;
//    }


}
