<?php

namespace App\Http\Controllers\Site;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class CategoryController extends Controller
{
    public function categoryList(){

    }

    public function categoryProducts($alias){

        $categories = Category::where('is_active', '1')->orderBy('position','asc')->get();

        if($alias != 'all'){
            $category = Category::where(['is_active' => 1, 'alias' => $alias])->first();
            $categoryTitle = $category->title;
            $categoryAlias = $category->alias;
            $categoryDescription = $category->description_seo;
            $products = Product::where(['is_active' => '1', 'category_id' => $category->id])->orderBy('date','desc')->paginate(12);
        }
        else{
            $categoryTitle = "Все категории";
            $categoryDescription = 'Все категории';
            $categoryAlias = 'all';
            $products = Product::where(['is_active' => '1'])->orderBy('date','desc')->paginate(12);
        }

        if (Cookie::get('cart') !== null) {
            $cart = Cookie::get('cart');
        } else {
            $cart = [];
        }

        return view('site.category.index', compact(['products','categoryTitle','categoryDescription','categoryAlias','categories','cart']));
    }
}
