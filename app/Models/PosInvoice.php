<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'company_id',
        'pos_terminal_id',
        'session_id',
        'pos_cash_tray_id',
        'user_id',
        'finished_by_user_id',
        'client_id',
        'car_id',
        'currency_id',
        'discount_id',
        'exchange_rate',
        'invoice_number',
        'status',
        'subtotal',
        'tax_total',
        'discount_total',
        'custom_discount_total',
        'grand_total',
        'paid_total',
        'remaining_total',
        'change_total',
        'stock_applied',
        'note',
        'opened_at',
        'closed_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PARTIAL = 'PARTIAL';
    public const STATUS_PAID = 'PAID';
    public const STATUS_REFUNDED = 'REFUNDED';
    public const STATUS_CANCELLED = 'CANCELLED';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'session_id');
    }

    public function cashTray()
    {
        return $this->belongsTo(PosCashTray::class);
    }

    public function posTerminal()
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function finishedByUser()
    {
        return $this->belongsTo(User::class, 'finished_by_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Lines
    |--------------------------------------------------------------------------
    */

    public function lines()
    {
        return $this->hasMany(PosInvoiceLine::class);
    }
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        return $this->hasMany(PosPayment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function scopeFilter($query, string $filter, string $searchByStatus)
    {
        if ($searchByStatus) {
            $query->where('status', '=', $searchByStatus);
        }


        $query->where(function ($query) use ($filter) {
            $query->where('invoice_number', 'like', '%' . $filter . '%')
                // ->orWhere('doc_number', 'like', '%' . $filter . '%')

                ->orWhereHas('user', function ($query) use ($filter) {
                    $query->where('name', 'like', '%' . $filter . '%');
                });

        });
    }
}
