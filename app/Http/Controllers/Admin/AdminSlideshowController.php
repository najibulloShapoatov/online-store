<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminSlideshowRequest;
use App\Models\Slideshow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminSlideshowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $slides = Slideshow::orderBy('date','desc')->get();
        return view('admin.slideshow.index', compact('slides'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.slideshow.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminSlideshowRequest $request)
    {
        $input = $request->all();
        if($file = $request->file('image')){
            $name = 'slide_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);
        }
        $slideshow = Slideshow::create($input);
        $slideshow->photo()->create(['image'=>$name]);
        return redirect('/admin/slideshow')->with(['success_message' => 'Успешно!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $slide = Slideshow::findOrFail($id);
        return view('admin.slideshow.show', compact('slide'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $slide = Slideshow::findOrFail($id);
        return view('admin.slideshow.edit', compact('slide'));
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
        $slide = Slideshow::findOrFail($id);

        if($file = $request->file('image')){

            $messages = [
                'date.required' => 'Введите дату',
                'image.required' => 'Загрузите картину слайда',
                'image.dimensions' => 'Картина доллжна быть 800x500 px',
                'image.mimes' => 'Формат картины должен быть (jpeg,png,jpg,gif)',
                'image.max' => 'Размер картины должна быть менее 1 МБ',
                'image.image' => 'Эй, вы че? Загрузите картину!',
            ];

            $this->validate($request, [
                'date' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:width=800,height=500'
            ],$messages);

            $name = 'slide_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);
            $photo = $slide->photo()->where(['imageable_id'=>$id])->first();
            if(!empty($photo->image)){
                $photo->image = $name;
                $photo->save();
            }
            else{
                $slide->photo()->create(['image'=>$name, 'imageable_id' => $id]);
            }
        }
        else{
            $messages = [
                'date.required' => 'Введите дату',
            ];

            $this->validate($request, [
                'date' => 'required',
            ],$messages);
        }

        if(empty($input['is_active'])){ $input['is_active'] = '0'; }

        $slide->update($input);
        return redirect('admin/slideshow')->with(['success_message' => 'Сохранена!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $slide = Slideshow::findOrFail($id);
        $photo = $slide->photo()->where(['imageable_id' => $id])->first();
        if(!empty($photo->image)){
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
        }
        $slide->delete();
        return redirect('/admin/slideshow')->with(['success_message' => 'Удален!']);
    }

    public function deleteimg(Request $request){
        if( $request->ajax() ) {
            $input = $request->all();
            $slide = Slideshow::findOrFail($input['id']);
            $photo = $slide->photo()->where(['imageable_id' => $input['id']])->first();
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
            $msg = "ok";
            return response()->json(array('msg'=> $msg), 200);
        }
    }
}