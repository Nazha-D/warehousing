<?php

namespace App\Models;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use HasFactory, NodeTrait, SoftDeletes;
    protected $fillable=['category_name','company_id'];
//
//
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
    protected $hidden = ['childrenRecursive']; // hide the original relation

    public function getChildrenAttribute()
    {
        return $this->childrenRecursive; // just rename
    }
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }
//    public function items()
//    {
//        return $this->hasMany(Item::class);
//    }
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('category_name', 'like', '%' . $filter . '%');
        }
    }
}
