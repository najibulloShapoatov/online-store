<?php

namespace App\Http\Controllers\Site;

use App\Models\Category;
use App\Models\Preorder;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Symfony\Component\DomCrawler\Crawler;

class ProductController extends Controller
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

    public function productList()
    {
        $products = Product::where('is_active', '1')->orderBy('date','desc')->paginate(12);

        $categories = Category::where('is_active', '1')->orderBy('position', 'asc')->get();

        if (Cookie::get('cart') !== null) {
            $cart = Cookie::get('cart');
        } else {
            $cart = [];
        }

        return view('site.products.list', compact(['products', 'categories', 'cart']));

    }

    public function productDetail($alias)
    {
        $data = Product::where('alias', $alias)->first();
        $colors = $this->colors();

        if($data != ''){
            $categories = Category::where('is_active', '1')->orderBy('position','asc')->get();
            $arrRel = explode(',', $data->related);
            $related = Product::where('id', '!=' , $data->id)->where('is_active', '1')->whereIn('category_id', $arrRel)->take(8)->get();

            if (Cookie::get('cart') !== null) {
                $cart = Cookie::get('cart');
            } else {
                $cart = [];
            }

            // viewed products
            Cookie::queue('productView['.time().']', $data->id, 60);
            $vp = [];
            if (Cookie::get('productView') !== null) {
                $productView = Cookie::get('productView');
            } else {
                $productView = [];
            }
            krsort($productView);
            if(!empty($productView)){
                $i=0;
                foreach($productView as $item){
                    if($i <= 10){
                        $product = Product::where('id',$item)->first();
                        $vp[$item] = $product;
                        $vp[$item]['image'] = $product->photo;
                        $vp[$item]['category'] = $product->category;
                    }
                    $i++;
                }
            }

            return view('site.products.detail', compact(['data','related','categories','cart','colors','vp']));
        }
        else{
            return view('errors.404');
        }

    }

    // parse reviews from ru-mi.com
    public function productReviews(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            //$link = 'https://ru-mi.com/ochki-virtualnoy-realnosti-xiaomi-mi-vr-play-2/reviews';
            $link = $input['link'];
            $html = file_get_contents($link);   // Get html remote text.
            $crawler = new Crawler(null, $link);    // Create new instance for parser.
            $crawler->addHtmlContent($html, 'UTF-8');

            $reviewAuthors = $crawler->filter('.one-review .one-review-left-author')->each(function (Crawler $node, $i) {
                return $node->text();
            });

            $reviewDate = $crawler->filter('.one-review .one-review-left-date')->each(function (Crawler $node, $i) {
                return $node->text();
            });

            $reviewRating = $crawler->filter('.one-review .rating_stars .rating_full')->each(function (Crawler $node, $i) {
                return $node->attr('style');
            });

            $reviewText = $crawler->filter('.one-review .one-review-right')->each(function (Crawler $node, $i) {
                return $node->html();
            });

            /*echo "<pre>";
            print_r($reviewText);
            echo "</pre>";*/

            $sts = '1';
            $html = View::make('site._reviews', compact(['reviewAuthors','reviewDate','reviewRating','reviewText']))->render();
            return response()->json(array('sts' => $sts, 'html' => $html), 200);

        }
    }

    public function filter(Request $request)
    {
        $input = $request->all();
        //dd($input);

        $priceMin = $input['price-min'];
        $priceMax = $input['price-max'];

        $categories = Category::where('is_active', '1')->orderBy('position', 'asc')->get();

        if (Cookie::get('cart') !== null) {
            $cart = Cookie::get('cart');
        } else {
            $cart = [];
        }

        if(!empty($input['checkbox'])){
            $products = Product::where('is_active', 1)
                ->whereIn('category_id', $input['checkbox'])
                ->whereBetween('price', [$priceMin, $priceMax])
                ->get();
        }
        else{
            $products = Product::where('is_active', 1)
                ->whereBetween('price', [$priceMin, $priceMax])
                ->get();
        }

        return view('site.products.filter', compact(['products', 'categories', 'cart']));
    }

    public function preorder(Request $request)
    {
        if( $request->ajax() )
        {
            $input = $request->all();
            $id = $input['id'];

            $product = new Product();
            $data = $product->getByID($id);

            $html = View::make('site._preorder', compact(['data']))->render();

            return response()->json(['html' => $html], 200);
        }
    }

    public function makePreorder(Request $request)
    {
        if( $request->ajax() )
        {
            $input = $request->all();

            $preoder = new Preorder();
            $data = $preoder->makePreorder($input);

            if($data){
                $sts = 1;
            }
            else{
                $sts = 0;
            }

            return response()->json(['sts' => $sts], 200);
        }
    }

}
