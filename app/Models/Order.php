<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_date',
        'name',
        'phone',
        'address',
        'order_list',
        'status',
        'payment_status',
        'payment_system',
        'itogo',
        'transaction_id'
    ];




}
