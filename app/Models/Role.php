<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;
class Role  extends SpatieRole
{
    use HasFactory;
    protected $fillable=
        ['name','company_id'];
    protected $guard_name = 'web';
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            $role->guard_name = 'web';
        });

        static::updating(function ($role) {
            $role->guard_name = 'web';
        });
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('name', 'like', '%' . $filter . '%');
        }
    }
}
