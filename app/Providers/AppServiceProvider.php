<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Infomenu;
use App\Models\Mainmenu;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Preorder;
use App\Models\Saleblock;
use App\Models\Settings;
use App\Models\Slideshow;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    // Склонение существительного на php - Товар, Товара, Товаров
    public static function sklonenie($n) {
        $forms = array('товар', 'товара', 'товаров');
        return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
    }

    /*private $from;
    private $phone;

    public function setToApp($from, $phone){
        $this->from = $from;
    }*/

    public function boot()
    {
        // admin side
        View::composer('layouts/admin', function ($view) {

            $categories = Category::orderBy('position','asc')->get();
            $orders = Order::where('status','0')->count();
            $preorders = Preorder::where('status','0')->count();

            $view->with([
                'cats' => $categories,
                'orders' => $orders,
                'preorders' => $preorders
            ]);

        });

        // site side
        View::composer('layouts/site', function ($view) {

            // cart
            $cart = Cookie::get('cart');
            $cartCount = Cookie::get('count');
            $cartPrice = Cookie::get('price');
            //print_r($cart);

            $kol = 0;
            $summa = 0;
            $sklon = '';
            if(!empty($cart)){
                foreach ($cart as $item){
                    $kol = $kol + $cartCount[$item];
                    $summa = $summa + ($cartCount[$item] * $cartPrice[$item]);
                }
                $sklon = $this->sklonenie($kol);
            }

            $setting = Settings::findOrFail(1);                                                                     // settings
            $categories = Category::where('is_active', '1')->orderBy('position','asc')->get();                      // categories
            $mainmenu = Mainmenu::where(['is_active' => 1, 'type' => 1])->orderBy('position','asc')->get();         // mainmenu
            $footermenu = Mainmenu::where(['is_active' => 1, 'type' => 2])->orderBy('position','asc')->get();       // footermenu
            $slides = Slideshow::where('is_active', '1')->orderBy('date','desc')->get();                            // slideshow
            $saleblock = Saleblock::where('is_active', '1')->orderBy('date','desc')->take(2)->get();                // saleblock
            $partners = Partner::where('is_active', '1')->orderBy('position','asc')->take(6)->get();                         // partners

            $view->with([
                'kol' => $kol,
                'summa' => $summa,
                'sklon' => $sklon,
                'setting' => $setting,
                'categories' => $categories,
                'mainmenu' => $mainmenu,
                'footermenu' => $footermenu,
                'slides' => $slides,
                'saleblock' => $saleblock,
                'partners' => $partners,
            ]);

        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
