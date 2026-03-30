<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReplenishmentLine extends Model
{
    protected $fillable = [
        'replenishment_id',
        'item_id',
        'package_id',
        'quantity',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'quantity'  => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    /* ================= Relations ================= */

    public function replenishment(): BelongsTo
    {
        return $this->belongsTo(Replenishment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(
            StockMovement::class,
            'reference_id'
        )->where('reference_type', self::class);
    }
}
