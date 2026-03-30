<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'package_id',
        'warehouse_id',
        'item_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
        'reference_line_id',
        'reversed_from_id',
        'occurred_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'occurred_at' => 'datetime',
    ];

    /**
     * المستودع
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * المنتج
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * الشركة
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * المرجع (PurchaseOrder, Invoice, etc.)
     */
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) return null;

        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
