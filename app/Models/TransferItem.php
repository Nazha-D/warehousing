<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'item_id',
        'transferred_qty',
        'transferred_qty_package_id',
        'received_qty',
        'received_qty_package_id',
        'note',
    ];

    protected $casts = [
        'transferred_qty' => 'decimal:4',
        'received_qty' => 'decimal:4',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function transferredPackage()
    {
        return $this->belongsTo(Package::class, 'transferred_qty_package_id');
    }

    public function receivedPackage()
    {
        return $this->belongsTo(Package::class, 'received_qty_package_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Derived data – not stored)
    |--------------------------------------------------------------------------
    */

    public function getQtyDifferenceAttribute()
    {
        if ($this->received_qty === null) {
            return null;
        }

        return $this->transferred_qty - $this->received_qty;
    }
}
