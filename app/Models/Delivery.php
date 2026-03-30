<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'driver_id',
       // 'sales_invoice_id',
        'delivery_number',
        'reference',
        'date',
        'expected_delivery',
        'total',
        'pod_file_path',
        'reason',
        'status',
    ];

    // ---------------- Relations ----------------

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }


    public function deliveryLines()
    {
        return $this->hasMany(DeliveryLine::class);
    }

    // ---------------- Scopes ----------------

    public function scopeFilter($query, ?string $filter)
    {
        if (!$filter) {
            return $query;
        }

        return $query->where(function ($q) use ($filter) {
            $q->where('delivery_number', 'like', "%{$filter}%")
                ->orWhere('reference', 'like', "%{$filter}%")
                ->orWhere('status', 'like', "%{$filter}%")
                ->orWhereHas('client', function ($q) use ($filter) {
                    $q->where('name', 'like', "%{$filter}%");
                })
                ->orWhereHas('driver', function ($q) use ($filter) {
                    $q->where('name', 'like', "%{$filter}%");
                });
        });
    }
}
