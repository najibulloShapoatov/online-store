<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'date',
        'title',
        'alias',
        'description',
        'description_seo',
        'content',
        'specification',
        'review_link',
        'price',
        'sale',
        'new',
        'hit',
        'popular',
        'availability',
        'colors',
        'is_active',
        'related'
    ];

    public function photo(){
        return $this->morphOne('App\Models\Photo', 'imageable');
    }

    public function category(){
        return $this->belongsTo('App\Models\Category');
    }

    public function gallery(){
        return $this->hasMany('App\Models\Gallery');
    }

    public function getByID($id)
    {
        return $this->where('id', $id)->get()->first();
    }

}
