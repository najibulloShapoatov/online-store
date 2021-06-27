<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Infomenu extends Model
{
    protected $fillable = [
        'title', 'alias', 'position', 'is_active', 'content', 'description_seo'
    ];
}
