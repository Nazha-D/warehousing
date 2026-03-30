<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperComboItem
 */
class ComboItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_id',
        'item_id',
        'description',
        'unit_price',
        'quantity',
        'discount',
        'total',
    ];

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
