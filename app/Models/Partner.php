<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'position',
        'title',
        'link'
    ];

    public function photo(){
        return $this->morphOne('App\Models\Photo', 'imageable');
    }
}
