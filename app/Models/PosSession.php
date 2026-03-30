<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class PosSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'pos_terminal_id',
        'opened_by_user_id',
        'closed_by_user_id',
        'status',
        'session_number',
        'note',
        'opening_date',
        'closing_date',
    ];

    protected $casts = [
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function openedByUser()
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function posTerminal()
    {
        return $this->belongsTo(PosTerminal::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices()
    {
        return $this->hasMany(PosInvoice::class, 'session_id');
    }

    public function cashTrays()
    {
        return $this->hasMany(PosCashTray::class, 'pos_session_id');
    }

    public function wastes()
    {
        return $this->hasMany(Waste::class, 'session_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpen(Builder $query)
    {
        return $query->where('status', 'OPEN');
    }

    public function scopeClosed(Builder $query)
    {
        return $query->where('status', 'CLOSED');
    }

    public function scopeFilter(Builder $query, ?string $filter)
    {
        if (!$filter) {
            return $query;
        }

        return $query->where(function ($q) use ($filter) {
            $q->where('session_number', 'like', "%{$filter}%")
                ->orWhereHas('posTerminal', fn($q2) => $q2->where('name', 'like', "%{$filter}%"))
                ->orWhere('status', 'like', "%{$filter}%")
                ->orWhere('opening_date', 'like', "%{$filter}%")
                ->orWhere('closing_date', 'like', "%{$filter}%");
        });
    }

    public function scopeBetweenSessionNumbers(Builder $query, $start, $end)
    {
        return $query->whereBetween('session_number', [$start, $end]);
    }

    public function scopeBetweenSessionDates(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('opening_date', [$startDate, $endDate]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    // هل هذه الـ Session ما زالت مفتوحة؟
    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }

    // اغلاق الـ Session
    public function close(int $closedByUserId, ?string $note = null)
    {
        $this->status = 'CLOSED';
        $this->closed_by_user_id = $closedByUserId;
        $this->closing_date = now();
        if ($note) {
            $this->note = $note;
        }
        $this->save();
    }

    // جلب إجمالي المدفوعات النقدية والفواتير المرتبطة بالجلسة
    public function totalCashPayments(): float
    {
        return $this->invoices()
            ->where('status', 'PAID')
            ->sum('cash_amount'); // نفترض وجود عمود cash_amount في PosInvoice
    }

    // تحقق من وجود Session مفتوحة لنفس الـ POS
    public function posHasOpenSession(): bool
    {
        return $this->posTerminal->sessions()->open()->exists();
    }
}
