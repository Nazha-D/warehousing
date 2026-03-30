<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'date',
        'transfer_number',
        'manual_reference',
        'src_warehouse_id',
        'dest_warehouse_id',
        'sending_user_id',
        'receiving_user_id',
        'status',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'date' => 'date',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
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

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'src_warehouse_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'dest_warehouse_id');
    }
    public function sendingUser()
    {
        return $this->belongsTo(User::class,'sending_user_id');

    }
    public function receivingUser()
    {
        return $this->belongsTo(User::class,'receiving_user_id');

    }
    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }
}
