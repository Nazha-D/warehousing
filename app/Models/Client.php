<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperClient
 */
class Client extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_number',
        'type',
        'name',
        'client_company_id',
        'country',
        'city',
        'state',
        'zip',
        'street',
        'floor_and_building',
        'job_position',
        'phone_code',
        'phone_number',
        'mobile_code',
        'mobile_number',
        'reference',
        'email',
        'title',
        'tags',
        'tax_id',
        'website',
        'contact_type',
        'contact_name',
        'contact_country',
        'contact_city',
        'contact_state',
        'contact_zip',
        'contact_street',
        'contact_phone_code',
        'contact_phone_number',
        'contact_mobile_code',
        'contact_mobile_number',
        'contact_email',
        'salesperson_id',
        'payment_term_id',
        'pricelist_id',
        'note',
        'active',
        'granted_discount',
        'is_blocked',
        'show_on_pos',
        'is_cash_customer',
        'auto_generated_number'
    ];
protected $casts=[
    'is_cash_customer'=>'boolean'
]
;
    public function scopeFilter($query,  $filter)
    {
        if ($filter) {
            return $query->where('name', 'like', '%' . $filter . '%')
                ->orWhere('mobile_number','like', '%' . $filter . '%')
                ->orWhereHas('cars',function ($subQuery)use($filter)
                {
                    $subQuery->where('plate_number','like', '%' . $filter . '%');
                });
        }
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function scopePos($query)
    {
        return $query->where('show_on_pos', true);
    }


    public function clientCompany()
    {
        return $this->belongsTo(Client::class, 'client_company_id');
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
//
//    public function paymentTerm()
//    {
//        return $this->belongsTo(PaymentTerm::class);
//    }

    public function priceLists()
    {
        return $this->belongsTo(Pricelist::class);
    }

//    public function quotations()
//    {
//        return $this->hasMany(Quotation::class);
//    }
//
//    public function orders()
//    {
//        return $this->hasMany(Order::class);
//    }
    public function clientAddresses()
    {
        return $this->hasMany(ClientAddress::class);
    }
//    public function deliveryAddresses()
//    {
//        return $this->clientAddresses()->where('type','=',1);
//    }
//    public function contactAddresses()
//    {
//        return $this->clientAddresses()->where('type','=',2);
//    }
    public  function cars()
    {
        return $this->hasMany(Car::class);
    }
//    public  function salesOrders()
//    {
//        return $this->hasMany(SalesOrder::class,'client_id');
//    }
}
