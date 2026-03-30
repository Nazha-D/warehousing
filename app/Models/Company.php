<?php

namespace App\Models;

use App\Http\Controllers\TermsController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Boolean;

class Company extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'email',
        'phone_code',
        'phone_number',
        'address',
        'is_active',
        'exchange_rate_mode',
        'has_garage'
    ];
    protected $casts=[
        'is_active'=>'boolean',
        'has_garage'=>'boolean',
        'exchange_rate_mode'=>\App\Enums\ExchangeRateModeEnum::class
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function currencies()
    {
        return $this->belongsToMany(Currency::class,
            'company_currencies', 'company_id',
            'currency_id')->withPivot('is_default', 'is_pos_currency');
    }
    public function salespeople()
    {
        return $this->users()->where('is_salesperson', true);
    }
    public function taxationGroups()
    {
        return $this->hasMany(TaxationGroup::class);
    }

    public function companyHeaders()
    {
        return $this->hasMany(CompanyHeader::class);
    }

    public function termsAndConditions()
    {
        return $this->hasMany(TermsAndCondition::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }
    public function priceLists()
    {
        return $this->hasMany(PriceList::class);
    }
    public function paymentTerms()
    {
        return $this->hasMany(PaymentTerm::class);
    }
    public function commissionMethods()
    {
        return $this->hasMany(CommissionMethod::class);
    }
    public function cashingMethods()
    {
        return $this->hasMany(CashingMethod::class);
    }
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
