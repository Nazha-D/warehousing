<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'stock_reservation_id',
        'item_id',
        'line_type_id',
        'item_id',
        'combo_id',
        'note',
        'description',
        'image',

        'unit_price',

        'package_name',

        'warehouse_id',
        'qty',
        'total',
        'invoiced_qty'
    ];

    // ---------------- Relations ----------------

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function reservation()
    {
        return $this->belongsTo(StockReservation::class, 'stock_reservation_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function SalesOrderLine()
    {
        return $this->belongsTo(SalesOrderLine::class);
    }
}
