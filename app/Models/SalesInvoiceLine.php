<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceLine extends Model
{
    protected $table = 'sales_invoice_lines';

    protected $fillable = [
        'sales_invoice_id',
        'delivery_line_id',
        'line_type_id',

        'item_id',
        'description',
        'quantity',
        'unit_price',
        'discount',

        'combo_id',
'total',
        'note',
        'image',
        'order_index',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function deliveryLine()
    {
        return $this->belongsTo(DeliveryLine::class, 'delivery_line_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }
}
