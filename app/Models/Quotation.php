<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\QuotationStatusEnum;
class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'quotation_number',
        'reference',
        'client_id',
        'terms_and_conditions_id',
        'validity',
        'input_date',
        'payment_term_id',
        'price_list_id',
        'currency_id',
        'terms_and_conditions',
        'salesperson_id',
        'commission_method_id',
        'cashing_method_id',
        'commission_rate',
        'commission_total',
        'total_before_vat',
        'special_discount',
        'special_discount_amount',
        'global_discount',
        'global_discount_amount',
        'vat',
        'vat_lebanese',
        'total',
        'status',
        'vat_exempt',
        'not_printed',
        'printed_as_vat_exempt',
        'printed_as_percentage',
        'vat_inclusive_prices',
        'before_vat_prices',
        'code',
        'title',
        'chance',
        'delivery_term_id',
        'cancellation_reason',
        'company_header_id'
    ];

    protected $casts = [
        'input_date' => 'date',
        'valid_until' => 'date',
        'vat_inclusive_prices' => 'boolean',
        'vat_exempt' => 'boolean',
        'not_printed' => 'boolean',
        'printed_as_vat_exempt' => 'boolean',
        'printed_as_percentage' => 'boolean',
        'before_vat_prices' => 'boolean',
        'status' => QuotationStatusEnum::class,
    ];

    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('quotation_number', 'like', '%' . $filter . '%')
                ->orWhere('reference', 'like', '%' . $filter . '%')
                ->orWhere('status', 'like', '%' . $filter . '%')
                ->orWhereHas('client',function ($query)use ($filter){
                    return $query->where('name',$filter);
                })  ->orWhereHas('currency',function ($query)use ($filter){
                    return $query->where('name',$filter);
                });

        }
    }

    public function scopePending($query)
    {
        return $query->where('status', QuotationStatusEnum::Pending);
    }

    public function scopeSent($query)
    {
        return $query->where('status', QuotationStatusEnum::Sent);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', QuotationStatusEnum::Cancelled);
    }
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function lines()
    {
        return $this->hasMany(QuotationLine::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }
    public function deliveryTerm()
    {
        return $this->belongsTo(DeliveryTerm::class);
    }
    public function companyHeader()
    {
        return $this->belongsTo(CompanyHeader::class);
    }
    public function commissionMethod()
    {
        return $this->belongsTo(CommissionMethod::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function salesPerson()
    {
        return $this->belongsTo(User::class,'salesperson_id');
    }

    public function termsAndConditions()
    {
        return $this->belongsTo(TermsAndCondition::class);
    }

    public function quotationLines()
    {
        return $this->hasMany(QuotationLine::class);
    }

}

