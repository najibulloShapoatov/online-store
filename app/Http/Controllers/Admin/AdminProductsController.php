<?php

namespace App\Http\Controllers\Admin;

use Ajaxray\PHPWatermark\Watermark;
use App\Http\Requests\AdminProductRequest;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminProductsController extends Controller
{
    public static function colors(){
        $colors = [
            'red'=>'Красный',
            'yellow'=>'Желтый',
            'black'=>'Черный',
            'blue'=>'Синий',
            'grey'=>'Серый',
            'pink'=>'Розовый',
            'white'=>'Белый',
            'green'=>'Зеленый',
        ];

        return $colors;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::orderBy('date','desc')->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::pluck('title', 'id');
        $categoryList = Category::all();
        $colors = $this->colors();
        return view('admin.products.create', compact(['categories','categoryList','colors']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminProductRequest $request)
    {
        $input = $request->all();
        if($file = $request->file('image')){
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);

            // watermark
            $imgMark = new Watermark('public/uploads/product_watermark.png');
            $imgMark->setOpacity(.3)->setStyle(Watermark::STYLE_IMG_DISSOLVE);
            $markedImg = 'prod_' . time() . '.' .$file->getClientOriginalExtension();
            $imgMark->withImage('public/uploads/'.$name, 'public/uploads/' . $markedImg);
            if(file_exists('public/uploads/' . $name)) {
                unlink('public/uploads/' . $name);
            }

            $input['image'] = $name;
        }

        if(empty($input['new'])){ $input['new'] = '0'; }
        if(empty($input['hit'])){ $input['hit'] = '0'; }
        if(empty($input['popular'])){ $input['popular'] = '0'; }
        if(empty($input['availability'])){ $input['availability'] = '0'; }

        // related products category
        if(!empty($input['related'])){
            $input['related'] = implode(',',$input['related']);
        }
        else{
            $input['related'] = '';
        }

        // colors
        if(!empty($input['colors'])){
            $input['colors'] = implode(',',$input['colors']);
        }
        else{
            $input['colors'] = '';
        }

        // save data
        $product = Product::create($input);

        // save image
        $product->photo()->create(['image'=>$markedImg]);

        // save gallery image
        if(!empty($input['inputGallery'])){
            foreach($input['inputGallery'] as $item){
                $product->gallery()->create(['image'=>$item]);
            }
        }

        return redirect('/admin/products')->with(['success_message' => 'Успешно!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $categoryList = Category::all();
        $colors = $this->colors();
        return view('admin.products.show', compact(['product','categoryList','colors']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categories = Category::pluck('title', 'id');
        $categoryList = Category::all();
        $product = Product::findOrFail($id);
        $colors = $this->colors();
        return view('admin.products.edit', compact(['categories','product','categoryList','colors']));
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
        $product = Product::findOrFail($id);

        if($file = $request->file('image')){

            $messages = [
                'date.required' => 'Введите дату',
                'category_id.required' => 'Выберите категорию',
                'title.required' => 'Введите заголовок',
                'title.unique' => 'Заголовок должен быть уникальным',
                'alias.required' => 'Введите алиас',
                'alias.unique' => 'Алиас должен быть уникальным',
                'description.required' => 'Введите описание товара',
                'content.required' => 'Введите контент товара',
                'specification.required' => 'Введите характеристики товара',
                'price.required' => 'Введите стоимость',
                'sale.numeric' => 'Поле скидка должна быть числом',
                'image.required' => 'Загрузите картину слайда',
                'image.dimensions' => 'Картина доллжна быть 800x500 px',
                'image.mimes' => 'Формат картины должен быть (jpeg,png,jpg,gif)',
                'image.max' => 'Размер картины должна быть менее 1 МБ',
                'image.image' => 'Эй, вы че? Загрузите картину!',
            ];

            $this->validate($request, [
                'date' => 'required|date|date_format:Y-m-d',
                'category_id' => 'required',
                'title' => 'required|unique:products,title,' . $product->id,
                'alias' => 'required|unique:products,alias,' . $product->id,
                'description' => 'required',
                'content' => 'required',
                'specification' => 'required',
                'price' => 'required',
                'sale' => 'numeric|nullable',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:width=600,height=600'
            ],$messages);

            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move('public/uploads', $name);

            // watermark
            $imgMark = new Watermark('public/uploads/product_watermark.png');
            $imgMark->setOpacity(.3)->setStyle(Watermark::STYLE_IMG_DISSOLVE);
            $markedImg = 'prod_' . time() . '.' .$file->getClientOriginalExtension();
            $imgMark->withImage('public/uploads/'.$name, 'public/uploads/' . $markedImg);
            if(file_exists('public/uploads/' . $name)) {
                unlink('public/uploads/' . $name);
            }

            $photo = $product->photo()->where(['imageable_id'=>$id])->first();
            if(!empty($photo->image)){
                $photo->image = $markedImg;
                $photo->save();
            }
            else{
                $product->photo()->create(['image'=>$markedImg, 'imageable_id' => $id]);
            }
        }
        else{
            $messages = [
                'date.required' => 'Введите дату',
                'category_id.required' => 'Выберите категорию',
                'title.required' => 'Введите заголовок',
                'title.unique' => 'Заголовок должен быть уникальным',
                'alias.required' => 'Введите алиас',
                'alias.unique' => 'Алиас должен быть уникальным',
                'description.required' => 'Введите описание товара',
                'content.required' => 'Введите контент товара',
                'specification.required' => 'Введите характеристики товара',
                'price.required' => 'Введите стоимость',
                'price.numeric' => 'Поле стоимость должна быть числом, например: 10.5 или 10',
                'sale.numeric' => 'Поле скидка должна быть числом',
            ];

            $this->validate($request, [
                'date' => 'required|date|date_format:Y-m-d',
                'category_id' => 'required',
                'title' => 'required|unique:products,title,' . $product->id,
                'alias' => 'required|unique:products,alias,' . $product->id,
                'description' => 'required',
                'content' => 'required',
                'specification' => 'required',
                'price' => 'required|numeric',
                'sale' => 'numeric|nullable',
            ],$messages);
        }

        if(empty($input['is_active'])){ $input['is_active'] = '0'; }
        if(empty($input['new'])){ $input['new'] = '0'; }
        if(empty($input['hit'])){ $input['hit'] = '0'; }
        if(empty($input['popular'])){ $input['popular'] = '0'; }
        if(empty($input['availability'])){ $input['availability'] = '0'; }

        // related
        if(!empty($input['related'])){
            $input['related'] = implode(',',$input['related']);
        }
        else{
            $input['related'] = '';
        }

        // colors
        if(!empty($input['colors'])){
            $input['colors'] = implode(',',$input['colors']);
        }
        else{
            $input['colors'] = '';
        }

        $product->update($input);
        return redirect('admin/products')->with(['success_message' => 'Сохранен!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // remove photo
        $photo = $product->photo()->where(['imageable_id' => $id])->first();
        if(!empty($photo->image)){
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
                $photo->delete();
            }
        }

        // remove product gallery photo
        $gal = $product->gallery()->where(['product_id' => $id])->get();
        if(count($gal) > 0){
            foreach($gal as $g){
                if(file_exists('public/uploads/gallery/' . $g->image)) {
                    unlink('public/uploads/gallery/' . $g->image);
                    $g->delete();
                }
            }
        }

        // remove product from db
        $product->delete();
        return redirect('/admin/products')->with(['success_message' => 'Удален!']);
    }

    public function productsByCategory($catID){
        $products = Product::where('category_id', $catID)->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function deleteimg(Request $request){
        if( $request->ajax() ) {
            $input = $request->all();
            $product = Product::findOrFail($input['id']);
            $photo = $product->photo()->where(['imageable_id' => $input['id']])->first();
            if(file_exists('public/uploads/' . $photo->image)) {
                unlink('public/uploads/' . $photo->image);
            }
            $photo->delete();
            $msg = "ok";
            return response()->json(array('msg'=> $msg), 200);
        }
    }

    public function ajaxUploadImage(Request $request){
        if( $request->ajax() ) {
            if($file = $request->file('file')){
                $input = $request->all();
                // create type
                if($input['id'] == 'add'){
                    $name = time() . '.' . $file->getClientOriginalExtension();
                    $file->move('public/uploads/gallery', $name);

                    // watermark
                    $imgMark = new Watermark('public/uploads/product_watermark.png');
                    $imgMark->setOpacity(.3)->setStyle(Watermark::STYLE_IMG_DISSOLVE);
                    $markedImg = time() . 'w.' .$file->getClientOriginalExtension();
                    $imgMark->withImage('public/uploads/gallery/'.$name, 'public/uploads/gallery/' . $markedImg);
                    if(file_exists('public/uploads/gallery/' . $name)) {
                        unlink('public/uploads/gallery/' . $name);
                    }

                    return response()->json(array('img' => $markedImg, 'id' => time()), 200);
                }
                // edit type
                else{
                    $name = time() . '.' . $file->getClientOriginalExtension();
                    $file->move('public/uploads/gallery', $name);

                    // watermark
                    $imgMark = new Watermark('public/uploads/product_watermark.png');
                    $imgMark->setOpacity(.3)->setStyle(Watermark::STYLE_IMG_DISSOLVE);
                    $markedImg = time() . 'w.' .$file->getClientOriginalExtension();
                    $imgMark->withImage('public/uploads/gallery/'.$name, 'public/uploads/gallery/' . $markedImg);
                    if(file_exists('public/uploads/gallery/' . $name)) {
                        unlink('public/uploads/gallery/' . $name);
                    }

                    $product = Product::where('id',$input['id'])->first();
                    $gallery = $product->gallery()->create(['image'=>$markedImg, 'product_id' => $product->id]);
                    return response()->json(array('img' => $markedImg, 'id' => $gallery->id), 200);
                }
            }
        }
    }

    public function ajaxRemoveImage(Request $request){
        if( $request->ajax() ) {
            $input = $request->all();

            // create type
            if($input['process'] == '2'){
                if(file_exists('public/uploads/gallery/' . $input['id'])) {
                    unlink('public/uploads/gallery/' . $input['id']);
                }
                $id = explode('.', $input['id']);
                return response()->json(array('sts' => 'ok', 'id' => $id[0]), 200);
            }
            // edit type
            else{
                $gallery = Gallery::findOrFail($input['id']);
                if(file_exists('public/uploads/gallery/' . $gallery->image)) {
                    unlink('public/uploads/gallery/' . $gallery->image);
                }
                $gallery->delete();
                return response()->json(array('sts' => 'ok', 'msg' => $input), 200);
            }
        }
    }

}