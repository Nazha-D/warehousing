<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosInvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_invoice_id',
        'item_id',
        'discount_id',
        'unit_price',
        'quantity',
        'discount_value',
        'custom_discount_value',
        'tax_value',
        'line_total'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->belongsTo(PosInvoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getSubtotalAttribute(): float
    {
        return round($this->unit_price * $this->quantity, 4);
    }

    public function getFinalTotalAttribute(): float
    {
        return round(
            ($this->unit_price * $this->quantity)
            - $this->discount_value
            + $this->tax_value,
            4
        );
    }
}
