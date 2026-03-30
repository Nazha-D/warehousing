<?php

namespace App\Models;

use App\Traits\Observable;
use App\Models\Scopes\SortByCodeScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class ItemGroup extends Model
{
    use HasFactory,NodeTrait, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'active',
    ];
    protected $appends=['show_name','root_name','children'];
    public function getShowNameAttribute()
    {

        if ($this->isRoot()) {
            return $this->code.''.$this->name;
        } else {
            return  $this->parent()->first()->show_name . '.' . $this->code . '' . $this->name;
        }



    }
    public function getRootNameAttribute()
    {

        if ($this->isRoot()) {
            return $this->code.''.$this->name;
        } else {
            return  $this->ancestors()->where('parent_id',null)->first()->name;
        }
    }
    //  protected $appends = ['children']; // will add this to the JSON output
    protected $hidden = ['childrenRecursive']; // hide the original relation

    public function getChildrenAttribute()
    {
        return $this->childrenRecursive; // just rename
    }

    protected static function booted()
    {
        //    static::addGlobalScope(new SortByCodeScope);

    }
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where(function ($query) use ($filter) {
                $query->where('code', 'like', '%' . $filter . '%')
                    ->orWhere('name', 'like', '%' . $filter . '%');
            });
        }
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_group_item');
    }
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

}
