<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'from_currency_id',
        'to_currency_id',
        'rate',
        'source_type',
        'updated_by_user_id',
    ];
    protected static function booted()
    {
        static::creating(function ($exchangeRate) {
            // إذا لم يُعطى from_currency_id، اجعله USD
            if (!$exchangeRate->from_currency_id) {
                $usd = Currency::where('code', 'USD')->first();
                $exchangeRate->from_currency_id = $usd?->id;
            }

            // يمكن وضع source_type افتراضي إذا أردت
            if (!$exchangeRate->source_type) {
                $exchangeRate->source_type = 'manual';
            }
        });
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

}
