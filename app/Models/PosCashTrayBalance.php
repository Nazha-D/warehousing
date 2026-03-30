<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCashTrayBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_cash_tray_id',
        'currency_id',
        'opening_amount',
        'expected_amount',
        'declared_closing_amount',
        'difference',
    ];

    /* ================= Relations ================= */

    public function cashTray()
    {
        return $this->belongsTo(PosCashTray::class, 'pos_cash_tray_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
