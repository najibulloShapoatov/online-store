<?php

namespace App\Http\Controllers\Admin;

use App\Models\Preorder;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminPreorderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Preorder::orderBy('order_date','desc')->paginate(15);
        return view('admin.preorders.index', compact('orders'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Preorder::findOrFail($id);
        return view('admin.preorders.edit', compact('order'));
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
        $order = Preorder::findOrFail($id);
        if(empty($input['status'])){ $input['status'] = '0'; }
        $order->update($input);
        return redirect('admin/preorders')->with(['success_message' => 'Успешно!']);
    }

    public function destroy($id)
    {
        $order = Preorder::findOrFail($id);
        $order->delete();
        return redirect('/admin/preorders')->with(['success_message' => 'Удален!']);
    }
}
