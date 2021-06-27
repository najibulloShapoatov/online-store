<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mainmenu extends Model
{
    protected $fillable = [
        'title', 'type', 'alias', 'position', 'is_active', 'content', 'description_seo'
    ];
}
