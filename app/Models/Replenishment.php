<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Replenishment extends Model
{
    protected $fillable = [
        'warehouse_id',
        'currency_id',
        'reference_type',
        'replenishment_number',
        'manual_reference',
        'date',
      'reference_id',
        'status',

        'created_by',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'date'        =>'date'
    ];

    /* ================= Relations ================= */

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ReplenishmentLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

     public function currency()
     {
         return $this->belongsTo(Currency::class);
     }
    /* ================= State helpers ================= */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
    public function stockMovements()
    {
        return $this->hasMany(
            StockMovement::class,
            'reference_id'
        )->where('reference_type', self::class);
    }
    public function hasStockMovements(): bool
    {
        return $this->stockMovements()->exists();
    }
}
