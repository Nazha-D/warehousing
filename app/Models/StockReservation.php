<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'item_id',
        'reserved_quantity',
        'source_type',
        'source_id',
        'status',
    ];

    /* ---------------------------------
     | Status constants
     |---------------------------------*/
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_CONSUMED  = 'consumed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED   = 'expired';

    /* ---------------------------------
     | Relationships
     |---------------------------------*/

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Polymorphic source
     * Example: SalesOrderLine
     */
    public function source()
    {
        return $this->morphTo();
    }

    /* ---------------------------------
     | Scopes
     |---------------------------------*/

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForItem($query, int $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
}
