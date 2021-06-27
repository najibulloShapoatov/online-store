<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saleblock extends Model
{
    protected $fillable = [
        'date',
        'title',
        'link',
        'is_active',
    ];

    public function photo(){
        return $this->morphOne('App\Models\Photo', 'imageable');
    }
}
