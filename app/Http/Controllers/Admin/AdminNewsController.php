<?php

namespace App\Http\Controllers\Admin;

use Ajaxray\PHPWatermark\Watermark;
use App\Http\Requests\AdminNewsRequest;
use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class AdminNewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $news = News::orderBy('date','desc')->paginate(15);
        return view('admin.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminNewsRequest $request)
    {
        $input = $request->all();
        if($file = $request->file('image')){
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);

            // watermark
            $imgMark = new Watermark('public/uploads/news_watermark.png');
            $imgMark->setOpacity(.3)->setStyle(Watermark::STYLE_IMG_DISSOLVE);
            $markedImg = 'news_' . time() . '.' .$file->getClientOriginalExtension();
            $imgMark->withImage('public/uploads/'.$name, 'public/uploads/' . $markedImg);
            if(file_exists('public/uploads/' . $name)) {
                unlink('public/uploads/' . $name);
            }

        }

        if($input['alias'] == ''){
            $input['alias'] = Str::slug($input['title']);
        }

        $news = News::create($input);
        $news->photo()->create(['image'=>$markedImg]);
        return redirect('/admin/news')->with(['success_message' => 'Успешно!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.edit', compact('news'));
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
        $news = News::findOrFail($id);

        if($file = $request->file('image')){

            $messages = [
                'date.required' => 'Введите дату',
                'title.required' => 'Введите заголовок',
                'alias.unique' => 'Алиас должен быть уникальным',
                'description.required' => 'Введите описание',
                'content.required' => 'Введите контент',
                'image.required' => 'Загрузите картину слайда',
                'image.dimensions' => 'Картина доллжна быть 800x500 px',
                'image.mimes' => 'Формат картины должен быть (jpeg,png,jpg,gif)',
                'image.max' => 'Размер картины должна быть менее 1 МБ',
                'image.image' => 'Эй, вы че? Загрузите картину!',
            ];

            $this->validate($request, [
                'date' => 'required|date|date_format:Y-m-d',
                'title' => 'required',
                'alias' => 'unique:news,alias,' . $news->id,
                'description' => 'required',
                'content' => 'required',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:width=800,height=500'
            ],$messages);

            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);

            // watermark
            $imgMark = new Watermark('public/uploads/news_watermark.png');
            $imgMark->setOpacity(.3)->setStyle(Watermark::STYLE_IMG_DISSOLVE);
            $markedImg = 'news_' . time() . '.' .$file->getClientOriginalExtension();
            $imgMark->withImage('public/uploads/'.$name, 'public/uploads/' . $markedImg);
            if(file_exists('public/uploads/' . $name)) {
                unlink('public/uploads/' . $name);
            }

            $photo = $news->photo()->where(['imageable_id'=>$id])->first();
            if(!empty($photo->image)){
                $photo->image = $markedImg;
                $photo->save();
            }
            else{
                $news->photo()->create(['image'=>$markedImg, 'imageable_id' => $id]);
            }
        }
        else{
            $messages = [
                'date.required' => 'Введите дату',
                'title.required' => 'Введите заголовок',
                'alias.required' => 'Введите алиас',
                'alias.unique' => 'Алиас должен быть уникальным',
                'description.required' => 'Введите описание',
                'content.required' => 'Введите контент',
            ];

            $this->validate($request, [
                'date' => 'required|date|date_format:Y-m-d',
                'title' => 'required',
                'alias' => 'required|unique:news,alias,' . $news->id,
                'description' => 'required',
                'content' => 'required',
            ],$messages);
        }

        if($input['alias'] == ''){
            $input['alias'] = Str::slug($input['title']);
        }

        if(empty($input['is_active'])){ $input['is_active'] = '0'; }

        $news->update($input);
        return redirect('admin/news')->with(['success_message' => 'Сохранена!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $photo = $news->photo()->where(['imageable_id' => $id])->first();
        if(!empty($photo->image)){
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
        }
        $news->delete();
        return redirect('/admin/news')->with(['success_message' => 'Удален!']);
    }

    public function deleteimg(Request $request){
        if( $request->ajax() ) {
            $input = $request->all();
            $news = News::findOrFail($input['id']);
            $photo = $news->photo()->where(['imageable_id' => $input['id']])->first();
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
            $msg = "ok";
            return response()->json(array('msg'=> $msg), 200);
        }
    }
}
