<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'lozung', 'address', 'phone', 'email', 'facebook_link', 'telegram_link', 'instagram_link', 'viber_link'
    ];
}
