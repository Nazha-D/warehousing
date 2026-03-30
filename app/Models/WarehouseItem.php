<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'item_id',
        'active',
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
}
