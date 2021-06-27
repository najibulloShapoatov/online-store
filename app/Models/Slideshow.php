<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slideshow extends Model
{
    protected $fillable = [
        'title', 'description', 'price', 'link', 'is_active', 'date'
    ];

    public function photo(){
        return $this->morphOne('App\Models\Photo', 'imageable');
    }
}
