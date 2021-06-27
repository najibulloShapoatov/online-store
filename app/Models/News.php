<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'date', 'title', 'alias', 'description', 'content', 'is_active', 'description_seo'
    ];

    public function photo(){
        return $this->morphOne('App\Models\Photo', 'imageable');
    }
}
