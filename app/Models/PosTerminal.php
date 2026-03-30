<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosTerminal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'name',
        'address',
        'pos_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sessions()
    {
        return $this->hasMany(PosSession::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeFilter(Builder $query, ?string $filter): Builder
    {
        if (!$filter) {
            return $query;
        }

        return $query->where(function ($q) use ($filter) {
            $q->where('name', 'like', "%{$filter}%")
                ->orWhere('address', 'like', "%{$filter}%")
                ->orWhere('pos_number', 'like', "%{$filter}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function hasOpenSession(): bool
    {
        return $this->sessions()
            ->where('status', 'OPEN')
            ->exists();
    }

    public function openSession()
    {
        return $this->sessions()
            ->where('status', 'OPEN')
            ->first();
    }
}
