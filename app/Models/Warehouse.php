<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'warehouse_number',
        'name',
        'type',
        'address',
        'blocked',
        'active',
    ];

    /**
     * الشركة التابعة لها المستودع
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * المنتجات المربوطة بالمستودع
     */
    public function items()
    {
        return $this->belongsToMany(
            Item::class,
            'warehouse_items',
            'warehouse_id',
            'item_id'
        )->withPivot('active')->withTimestamps();
    }

    /**
     * كل حركات المخزون في هذا المستودع
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('name', 'like', '%' . $filter . '%');
        }
    }
    public function outgoingTransfers()
    {
        return $this->hasMany(Transfer::class, 'src_warehouse_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(Transfer::class, 'dest_warehouse_id');
    }
}
