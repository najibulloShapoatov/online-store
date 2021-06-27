<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminPartnerRequest;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $partners = Partner::orderBy('position','asc')->paginate(15);
        return view('admin.partners.index', compact('partners'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.partners.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminPartnerRequest $request)
    {
        $input = $request->all();
        if($file = $request->file('image')){
            $name = 'p_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);
        }
        $partner = Partner::create($input);
        $partner->photo()->create(['image'=>$name]);
        return redirect('/admin/partners')->with(['success_message' => 'Успешно!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.show', compact('partner'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.edit', compact('partner'));
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
        $partner = Partner::findOrFail($id);

        if($file = $request->file('image')){

            $messages = [
                'title.required' => 'Введите заголовок',
                'position.required' => 'Введите позицию',
                'image.required' => 'Загрузите картину слайда',
                'image.dimensions' => 'Картина доллжна быть 159x37 px',
                'image.mimes' => 'Формат картины должен быть (jpeg,png,jpg,gif)',
                'image.max' => 'Размер картины должна быть менее 1 МБ',
                'image.image' => 'Эй, вы че? Загрузите картину!',
            ];

            $this->validate($request, [
                'title' => 'required',
                'position' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:width=159,height=37'
            ],$messages);

            $name = 'p_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);
            $photo = $partner->photo()->where(['imageable_id'=>$id])->first();
            if(!empty($photo->image)){
                $photo->image = $name;
                $photo->save();
            }
            else{
                $partner->photo()->create(['image'=>$name, 'imageable_id' => $id]);
            }
        }
        else{
            $messages = [
                'title.required' => 'Введите заголовок',
                'position.required' => 'Введите позицию',
            ];

            $this->validate($request, [
                'title' => 'required',
                'position' => 'required',
            ],$messages);
        }

        if(empty($input['is_active'])){ $input['is_active'] = '0'; }

        $partner->update($input);
        return redirect('admin/partners')->with(['success_message' => 'Сохранена!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);
        $photo = $partner->photo()->where(['imageable_id' => $id])->first();
        if(!empty($photo->image)){
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
        }
        $partner->delete();
        return redirect('/admin/partners')->with(['success_message' => 'Удален!']);
    }

    public function deleteimg(Request $request){
        if( $request->ajax() ) {
            $input = $request->all();
            $partner = Partner::findOrFail($input['id']);
            $photo = $partner->photo()->where(['imageable_id' => $input['id']])->first();
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
            $msg = "ok";
            return response()->json(array('msg'=> $msg), 200);
        }
    }
}
