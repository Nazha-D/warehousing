<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'quotation_id',
        'client_id',
        'currency_id',
        'price_list_id',
        'payment_term_id',
        'salesperson_id',
        'commission_method_id',
        'cashing_method_id',
        'company_header_id',
        'sales_order_number',
        'reference',
        'code',
        'title',
        'input_date',
        'validity',
        'special_discount',
        'special_discount_amount',
        'global_discount',
        'global_discount_amount',
        'vat',
        'vat_lebanese',
        'vat_exempt',
        'vat_inclusive_prices',
        'before_vat_prices',
        'total_before_vat',
        'total',
        'commission_rate',
        'commission_total',
        'not_printed',
        'printed_as_vat_exempt',
        'printed_as_percentage',
        'status',
        'terms_and_conditions',
    ];

/* ==============================
 | Relationships
 |============================== */

    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('sales_order_number', 'like', '%' . $filter . '%')
                ->orWhere('reference', 'like', '%' . $filter . '%')
                ->orWhere('status', 'like', '%' . $filter . '%')    ->orWhereHas('client',function ($query)use ($filter){
                    return $query->where('name',$filter);
                })  ->orWhereHas('currency',function ($query)use ($filter){
                    return $query->where('name',$filter);
                });
        }
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
         public function paymentTerm()
     {
        return $this->belongsTo(PaymentTerm::class);
     }
       public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
    public function commissionMethod()
    {
        return $this->belongsTo(CommissionMethod::class);
    }
    public function cashingMethod()
    {
        return $this->belongsTo(CashingMethod::class);
    }
    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class);
    }

    /* ==============================
     | Scopes
     |============================== */

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where('number', 'like', "%{$term}%");
    }

    /* ==============================
     | State helpers
     |============================== */

    public function isDraft(): bool
    {
        return $this->status === SalesOrderStatusEnum::DRAFT->value;
    }

    public function isConfirmed(): bool
    {
        return $this->status === SalesOrderStatusEnum::CONFIRMED->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === SalesOrderStatusEnum::CANCELLED->value;
    }
}
