<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderLine extends Model
{
    use HasFactory, SoftDeletes;





    protected $fillable = [
        'sales_order_id',
        'line_type_id',
        'title',
        'item_id',
        'description',
        'quantity',
        'warehouse_id',
        'unit_price',
        'discount',
        'total',
        'combo_id',
        'note',
        'image',
        'order_index',
];


protected $casts = [
    'quantity' => 'float',
    'unit_price' => 'float',
    'discount' => 'float',
    'total' => 'float',

];


protected $appends = [
    'combo_code',
    'item_name',
    'item_main_code',
];


/* --------------------------------------------
| Accessors
|---------------------------------------------*/


public function getItemMainCodeAttribute(): string
{
    return $this->item->main_code ?? '';
}


public function getComboCodeAttribute(): string
{
    return $this->combo->code ?? '';
}


public function getItemNameAttribute(): string
{
    return $this->item->item_name ?? '';
}


/* --------------------------------------------
| Relationships
|---------------------------------------------*/


public function salesOrder()
{
    return $this->belongsTo(SalesOrder::class);
}


public function lineType()
{
    return $this->belongsTo(LineType::class);
}


public function item()
{
    return   $this->belongsTo(Item::class);
}


public function combo()
{
    return $this->belongsTo(Combo::class);
}


public function orderLineDelivery()
{
    return $this->hasMany(DeliveryLine::class);
}
    public function stockReservations()
    {
        return $this->morphMany(
            StockReservation::class,
            'source'
        );
    }

}
