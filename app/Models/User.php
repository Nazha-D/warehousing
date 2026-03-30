<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_salesperson',
        'company_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_salesperson'=>'boolean',
        'is_active'=>'boolean'
    ];
    protected $guard_name = 'web';

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('name', 'like', '%'.$filter.'%');
        }
    }
    public  function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function roles():\Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'model_has_roles',
            'model_id',
            'role_id'
        );
    }
  public  function userSalespersonConfiguration()
  {
      return $this->hasOne(UserSalespersonConfiguration::class);
  }
    public function cashingMethod()
    {
        return $this->hasOneThrough(
            CashingMethod::class,
            UserSalespersonConfiguration::class,
            'user_id',
            'id',
            'id',
            'cashing_method_id'
        );
    }

    public function commissionMethod()
    {
        return $this->hasOneThrough(
            CommissionMethod::class,
            UserSalespersonConfiguration::class,
            'user_id',
            'id',
            'id',
            'commission_method_id'
        );
    }
}
