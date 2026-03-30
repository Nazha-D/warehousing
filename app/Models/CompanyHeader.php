<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class CompanyHeader extends Model
{
    use HasFactory;

    protected $fillable=[
        'company_id','header_name','logo',
        'full_company_name','address','mobile_code',
        'mobile_number','email','website','phone_code',
        'default_quotation_currency_id',
        'phone_number','trn','bank_info',
        'local_payments','vat','company_subject_to_vat'
    ];
    protected $casts=[
        'company_subject_to_vat'=>'boolean'
    ];
    protected $appends=['logo'];

    public function getLogoAttribute($value)
    {
        if (blank($this->attributes['logo'] ?? null)) {
            return null;
        }
        $path=env('APP_URL').'storage/app/public/';

        return $path.ltrim($this->attributes['logo'], '/');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public  function defaultQuotationCurrency()
    {
        return $this->belongsTo(Currency::class,'default_quotation_currency_id');
    }



}
