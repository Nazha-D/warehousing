<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'pos_invoice_id',
        'pos_session_id',
        'pos_cash_tray_id',
        'cashing_method_id',
        'currency_id',
        'payment_method',
        'amount',
        'exchange_rate',
        'amount_in_invoice_currency',
        'amount_in_company_currency',
        'type',
    ];

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    public const TYPE_PAYMENT = 'payment';
    public const TYPE_REFUND  = 'refund';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->belongsTo(PosInvoice::class,'pos_invoice_id');
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class);
    }

    public function cashTray()
    {
        return $this->belongsTo(PosCashTray::class);
    }

    public function cashingMethod()
    {
        return $this->belongsTo(CashingMethod::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }
}
