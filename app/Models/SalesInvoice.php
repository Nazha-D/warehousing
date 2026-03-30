<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id','payment_term_id','price_list_id',
        'currency_id','salesperson_id','commission_method_id',
        'cashing_method_id','sales_invoice_number','reference',
        'client_id','value_date','terms_and_conditions','commission_rate',
        'commission_total','special_discount','special_discount_amount',
        'global_discount','global_discount_amount','vat_lebanese',
        'vat','total','status','total_before_vat','vat_exempt',
        'not_printed','printed_as_vat_exempt','printed_as_percentage',
        'vat_inclusive_prices','before_vat_prices','code',
        'title','delivered_from_warehouse_id','invoice_delivery_date','input_date',
        'company_header_id','invoice_type','car_id','terms_and_condition_id'
    ];
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('sales_invoice_number', 'like', '%' . $filter . '%')
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
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function lines()
    {
        return $this->hasMany(SalesInvoiceLine::class, 'sales_invoice_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'delivered_from_warehouse_id');
    }
}
