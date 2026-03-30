<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosCashTray extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'pos_terminal_id',
        'pos_session_id',
        'user_id',
        'tray_number',
        'status',
        'opened_at',
        'closed_at',
    ];
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('tray_number', 'like', '%' . $filter . '%');
        }
    }

    /* ================= Relations ================= */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function posTerminal()
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function balances()
    {
        return $this->hasMany(PosCashTrayBalance::class, 'pos_cash_tray_id');
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class, 'pos_cash_tray_id');
    }

    /* ================= Scopes ================= */

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
