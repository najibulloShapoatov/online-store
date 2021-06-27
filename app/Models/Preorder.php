<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preorder extends Model
{
    protected $fillable = [
        'order_date',
        'product_id',
        'customer',
        'phone',
        'status'
    ];

    public function product(){
        return $this->belongsTo('App\Models\Product');
    }

    public function makePreorder($input)
    {
        $id = $input['id'];
        $name = $input['name'];
        $phone = $input['phone'];

        if(!empty($id) && !empty($name) && !empty($phone))
        {
            $p = new Preorder();
            $p->order_date = date('Y-m-d H:i:s');
            $p->product_id = $id;
            $p->customer = $name;
            $p->phone = $phone;
            return $p->save();
        }

        return false;

    }

}
