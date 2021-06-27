<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::orderBy('order_date','desc')->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $user = '';
        if(!empty($order->user_id)){
            $user = User::where('id',$order->user_id)->first();
        }
        return view('admin.orders.edit', compact(['order','user']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $order = Order::findOrFail($id);
        if(empty($input['status'])){ $input['status'] = '0'; }
        if(empty($input['payment_status'])){ $input['payment_status'] = '0'; }
        $order->update($input);
        return redirect('admin/orders')->with(['success_message' => 'Успешно!']);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return redirect('/admin/orders')->with(['success_message' => 'Удален!']);
    }
}
