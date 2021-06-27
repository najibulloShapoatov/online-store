<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminSaleblockRequest;
use App\Models\Saleblock;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminSaleblockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $saleblock = Saleblock::orderBy('date','desc')->paginate(15);
        return view('admin.saleblock.index', compact('saleblock'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.saleblock.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminSaleblockRequest $request)
    {
        $input = $request->all();
        if($file = $request->file('image')){
            $name = 'sale_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);
        }
        $saleblock = Saleblock::create($input);
        $saleblock->photo()->create(['image'=>$name]);
        return redirect('/admin/saleblock')->with(['success_message' => 'Успешно!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $saleblock = Saleblock::findOrFail($id);
        return view('admin.saleblock.show', compact('saleblock'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $saleblock = Saleblock::findOrFail($id);
        return view('admin.saleblock.edit', compact('saleblock'));
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
        $saleblock = Saleblock::findOrFail($id);
        if($file = $request->file('image')){
            $name = 'sale_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);
            $photo = $saleblock->photo()->where(['imageable_id'=>$id])->first();
            if(!empty($photo->image)){
                $photo->image = $name;
                $photo->save();
            }
            else{
                $saleblock->photo()->create(['image'=>$name, 'imageable_id' => $id]);
            }
        }
        if(empty($input['is_active'])){ $input['is_active'] = '0'; }
        $saleblock->update($input);
        return redirect('admin/saleblock')->with(['success_message' => 'Сохранен!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $saleblock = Saleblock::findOrFail($id);
        $photo = $saleblock->photo()->where(['imageable_id' => $id])->first();
        if(!empty($photo->image)){
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
        }
        $saleblock->delete();
        return redirect('/admin/saleblock')->with(['success_message' => 'Удален!']);
    }

    public function deleteimg(Request $request){
        if( $request->ajax() ) {
            $input = $request->all();
            $saleblock = Saleblock::findOrFail($input['id']);
            $photo = $saleblock->photo()->where(['imageable_id' => $input['id']])->first();
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
            $msg = "ok";
            return response()->json(array('msg'=> $msg), 200);
        }
    }

}
