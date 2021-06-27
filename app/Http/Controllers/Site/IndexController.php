<?php

namespace App\Http\Controllers\Site;

use App\Library\KortiMilli;
use App\Models\Category;
use App\Models\Mainmenu;
use App\Models\News;
use App\Models\Order;
use App\Models\Product;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class IndexController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // Склонение существительного на php - Товар, Товара, Товаров
    public static function sklonenie($n) {
        $forms = array('товар', 'товара', 'товаров');
        return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
    }

    public function homepage(Request $req)
    {
        //dd(Cookie::get('count'));
        $hitProds = Product::where(['hit' => 1, 'is_active' => 1])->orderBy('date','desc')->take(15)->get();
        $saleProds = Product::where('sale', '!=' , 0)->where('is_active',1)->orderBy('date','desc')->take(15)->get();
        $newProds = Product::where(['new' => 1, 'is_active' => 1])->orderBy('date','desc')->take(15)->get();
        $popularProds = Product::where(['popular' => 1, 'is_active' => 1])->orderBy('date','desc')->take(15)->get();
        $news = News::where('is_active', 1)->orderBy('date','desc')->take(6)->get(); // get with limit

        if (Cookie::get('cart') !== null) {
            $cart = Cookie::get('cart');
        } else {
            $cart = [];
        }

        $from = $req->from;
        $phone = $req->phone;

        if($from != '') {
            //Cookie::queue('from', $from, 30 * 60);
            setcookie('from', $from, 0, '/', '', false, false);
        }
        if($phone != '') {
            //Cookie::queue('phone', $phone, 30 * 60);
            setcookie('phone', $phone, 0, '/', '', false, false);
        }
        return view('site.index', compact([
            'hitProds',
            'saleProds',
            'newProds',
            'popularProds',
            'news',
            'cart',
            'from',
            'phone',
        ]));
    }

    public function pageDetail($alias)
    {
        $page = Mainmenu::where(['alias' => $alias, 'is_active' => 1])->first();
        $setting = Settings::findOrFail(1);

        if($page != ''){
            return view('site.page', compact(['page','setting']));
        }
        else{
            return view('errors.404');
        }
    }

    // search result
    public function searchResult($searchText)
    {
        $categories = Category::where('is_active', '1')->orderBy('position','asc')->get();

        $products = Product::where('is_active', 1)
            ->where('title', 'LIKE', '%' . $searchText . '%')
            ->orderBy('date','desc')
            ->paginate(12);

        $cart = [];
        if (Cookie::get('cart') !== null) {
            $cart = Cookie::get('cart');
        }

        return view('site.search', compact(['products','categories','cart']));
    }

    // add to cart
    public function addToCart(Request $request)
    {
        if( $request->ajax() ) {
            $input = $request->all();

            $id = $input['id'];
            $price = $input['price'];
            $count = $input['count'];
            //$minutes = time() + (86400); // 1 day

            return response()->json(array('sts' => 'added!'), 200)
                ->withCookie(cookie("cart[".$id."]", $id))
                ->withCookie(cookie("count[".$id."]", $count))
                ->withCookie(cookie("price[".$id."]", $price));
        }
    }

    // show cart (in popup)
    public function showCart(Request $request)
    {
        if ($request->ajax()) {

            // cart
            $cart = Cookie::get('cart');
            $cartCount = Cookie::get('count');
            $cartPrice = Cookie::get('price');

            $data = [];

            if(!empty($cart)){
                foreach($cart as $item){
                    $product = Product::where('id',$item)->first();
                    $data[$item] = $product;
                    $data[$item]['image'] = $product->photo->image;
                }
                $html = View::make('site._cart', compact(['data','cartCount']))->render();
            }
            else{
                $html = '<p>Пуста</p>';
            }
            return response()->json(array('html' => $html), 200);
        }
    }

    // change count of procducts item
    public function changeCartInput(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $id = $input['id'];
            $val = $input['val'];

            $cart = Cookie::get('cart');
            $cartCount = Cookie::get('count');
            $cartPrice = Cookie::get('price');

            $cartCount[$id] = $val;

            $summa = 0;
            foreach ($cart as $item){
                $summa = $summa + ($cartCount[$item] * $cartPrice[$item]);
            }

            return response()->json(array('sts' => 'ok', 'itogo' => $summa), 200)
                ->withCookie(cookie("count[".$id."]", $val));
        }
    }

    // refresh cart
    public function refreshCartOnTop(Request $request)
    {
        if ($request->ajax()) {
            $cart = Cookie::get('cart');
            $cartCount = Cookie::get('count');
            $cartPrice = Cookie::get('price');

            $kol = 0;
            $summa = 0;
            $sklon = '';
            if(!empty($cart)){
                foreach ($cart as $item){
                    $kol = $kol + $cartCount[$item];
                    $summa = $summa + ($cartCount[$item] * $cartPrice[$item]);
                }
                $sklon = $this->sklonenie($kol);
                $sts = '1';
            }
            else{
                $sts = '0';
            }

            return response()->json(array('sts' => $sts, 'kol' => $kol, 'summa' => $summa, 'sklon' => $sklon), 200);
        }
    }

    // remove product from cart
    public function removeProductFromCart(Request $request)
    {
        if ($request->ajax()) {

            $input = $request->all();
            $id = $input['id'];

            $cart = Cookie::get('cart');
            $cartCount = Cookie::get('count');
            $cartPrice = Cookie::get('price');

            // remove id from cart array
            unset($cart[$id]);
            unset($cartCount[$id]);
            unset($cartPrice[$id]);

            $summa = 0;
            if(!empty($cart)){
                foreach ($cart as $item){
                    $summa = $summa + ($cartCount[$item] * $cartPrice[$item]);
                }
                $sts = '1';
            }
            else{
                $sts = '0';
            }

            return response()->json(array('sts' => $sts, 'itogo' => $summa), 200)
                ->withCookie(Cookie::forget('cart['.$id.']'))
                ->withCookie(Cookie::forget('count['.$id.']'))
                ->withCookie(Cookie::forget('price['.$id.']'));
        }
    }

    // order
    public function orderCart(Request $request)
    {
        if ($request->ajax()) {

            // cart
            $cart = Cookie::get('cart');
            $cartCount = Cookie::get('count');
            $cartPrice = Cookie::get('price');

            $data = [];

            if(!empty($cart)){
                foreach($cart as $item){
                    $product = Product::where('id',$item)->first();
                    $data[$item] = $product;
                    $data[$item]['image'] = $product->photo->image;
                }
                $html = View::make('site._order', compact(['data','cartCount']))->render();
            }
            else{
                $html = '<p>Пуста</p>';
            }

            return response()->json(array('html' => $html), 200);
        }
    }

    // improve order and submit
    public function makeOrder(Request $request)
    {



            $input = $request->all();
            $name = $input['name'];
            $phone = $input['phone'];
            $address = $input['address'];
            $psys = $input['psys'];
            $delivery = $input['delivery'];



            $cart = Cookie::get('cart');
            $cartCount = Cookie::get('count');
            $cartPrice = Cookie::get('price');


            if(!empty($cart)){


                $srt = '';
                $data = [];
                $prodIDs = [];

                foreach($cart as $item){
                    $product = Product::where('id',$item)->first();
                    $data[$item] = $product;
                    $data[$item]['image'] = $product->photo->image;
                    $prodIDs[$item] = $item;
                }

                $srt .= '<div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Название</th>
                                <th>Цена</th>
                                <th>Кол-во</th>
                                <th>Всего</th>
                            </tr>
                        </thead>
                        <tbody>';
                            $sum = 0;
                            foreach($data as $item):
                                $srt .= '<tr id="cart-row-' . $item->id . '">
                                    <th scope="row">';
                                        if(!empty($item->photo->image)):
                                            $srt .= '<img src="/public/uploads/' . $item->image . '" width="50" alt="">';
                                        else:
                                            $srt .= '<p>No image</p>';
                                        endif;
                                $srt .= '</th>
                                    <td><a href="/products/' . $item->alias . '">' . $item->title . '</a></td>
                                    <td>' . $item->price . ' сом.</td>
                                    <td width="100">
                                        ' . $cartCount[$item->id] . ' шт.
                                    </td>
                                    <td width="110"><span id="int_sum_' . $item->id . '">' . $cartCount[$item->id] * $item->price . '</span> сом.</td>
                                </tr>';
                                $sum = $sum + ($cartCount[$item->id] * $item->price);
                            endforeach;
                                $srt .= '<tr>
                                        <th scope="row" colspan="4">Итого:</th>
                                        <td><strong><span id="itogo">' . $sum . '</span></strong> сом.</td>
                                    </tr>
                        </tbody>
                    </table>
                </div>';

                // remove cookie
                foreach($cart as $item)
                {
                    Cookie::queue(Cookie::forget('cart['.$item.']'));
                    Cookie::queue(Cookie::forget('count['.$item.']'));
                    Cookie::queue(Cookie::forget('price['.$item.']'));
                }

                // user id
                if(!empty(Auth::user()->id)){
                    $userID = Auth::user()->id;

                    // save user info when ordering
                    $user = User::where('id',$userID)->first();
                    $user->name = $name;
                    $user->phone = $phone;
                    $user->address = $address;
                    $user->save();
                }
                else{
                    $userID = '0';
                }






                // save order
                $order = new Order();
                $order->order_date = date("Y-m-d H:i:s");
                $order->user_id = $userID;
                $order->name = $name;
                $order->phone = $phone;
                $order->address = $address;
                $order->order_list = $srt;
                $order->itogo = $sum;
                $order->payment_system = $psys;
                $order->payment_status = 0;
                $order->delivery_type = $delivery;
                $result = $order->save();



                if($psys == '3'){

                    $jsondata = $this->myBabilon($sum, $prodIDs, $order->id, $cartCount, $delivery);

                    if($jsondata["result"] != 0){
                        $order->delete();
                    }
                    $order->name .=  "( myBabilon )";
                    $order->save();
                    return response()->json([
                        'sts' => "ok",
                        'psys' => $psys,
                        'prodIDs' => $prodIDs,
                        'json' => $jsondata,
                    ], 200);
                }



                if($result){

                    // sand to email
                    Mail::send('emails.orders', ['cartCount' => $cartCount, 'data' => $data, 'name' => $name, 'phone' => $phone, 'address' => $address], function($message)
                    {
                        $message->to('orders@astore.tj', 'aStore')->subject('Новый заказ на aStore');
                        $message->from('noreply.me8787@gmail.com', 'aStore');
                    });

                    // if success, alert "ok"
                    $sts = 'ok';

                    if($psys == '1'){
                        // oplata pri dostavki
                        return response()->json([
                            'sts' => $sts,
                            'psys' => '1',
                            'prodIDs' => $prodIDs,
                            'cart' => $cart
                        ], 200);
                    }
                    else{
                        // korti milli
                        $userID = Auth::user()->id;

                        $amount = sprintf('%.2f',$sum);
                        //$amount = sprintf('%.2f',1);

                        $kortiMilli = new KortiMilli('____','____________');
                        $kortiMilli->amount = $amount;
                        $kortiMilli->orderid = $order->id;
                        $kortiMilli->callbackUrl = 'https://_______';
                        $kortiMilli->phone = $phone;
                        $kortiMilli->email = Auth::user()->email;
                        $kortiMilli->info = 'Оплата с помощью Корти Милли';
                        $kortiMilli->infoHash = '';
                        $kortiMilli->returnUrl = 'https://__________/';
                        $token = $kortiMilli->token();

                        $html = View::make('site._orderKortiMilli', compact(['token','kortiMilli']))->render();
                        return response()->json([
                            'html' => $html,
                            'sts' => $sts,
                            'psys' => '2',
                            'prodIDs' => $prodIDs,
                            'cart' => $cart
                        ], 200);
                    }
                }
            }
            else{
                $sts = 'empty';
            }
    }

    public  function  myBabilon($sum, $prodIDs, $receiptID, $cartCount, $del){
    $data=[];
    //Market Place
    $marketPlaceId='_';
    $token='___';
    $delivery='__';
    
        $sum+=10;
    //////////////////
    ///Merchant
    $acquirerId='___';
    $merchantid='____';
    $mtoken='___';
    //////////////////
    $data["Sign"] = sha1($marketPlaceId.$token.$sum.$receiptID.'0');
    $data["MarketPlaceId"] = $marketPlaceId;
    $data["Sum"]=$sum;
    $data["ReceiptId"] = $receiptID;
    $data["Delivery"]=$delivery;
    //
    $Merchant=[];
        $Merchant["Sign"] = sha1($acquirerId.$merchantid.$mtoken.$sum.$receiptID);
        $Merchant["AcquirerId"] = $acquirerId;
        $Merchant["Merchantid"] = $merchantid;
        $Merchant["Sum"] = $sum;
        $Merchant["ReceiptId"] = $receiptID;

        $p=[];
        $p["Name"]='Доставка';
        $p["Count"]=1;
        $p["Sum"]=10 * 1;
        $Merchant["Goods"][] = $p;


    foreach($prodIDs as $pID){
        $product = new Product();
        $pr = $product->getByID($pID);
        $p=[];
        $p["Name"]=$pr->title;
        $p["Count"]=$cartCount[$pr->id];
        $p["Sum"]=$pr->price * $cartCount[$pr->id];
        $Merchant["Goods"][] = $p;
    }
    $data["Details"][] = $Merchant;

        return $this->curlReq('https://my1.babilon-m.tj/qrapi/MarketPlace/CreateTxn.aspx', $data);

    }

    public function curlReq($url, $data){
        //$url = "your url";
        $content = json_encode($data);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


        curl_close($curl);

        return json_decode($json_response, true);
    }







    // send message
    public function sendMsg(Request $request)
    {
        if ($request->ajax()) {

            $input = $request->all();
            $name = $input['name'];
            $email = $input['email'];
            $message = $input['message'];

            // sand to email
            /*Mail::send('emails.contacts', ['name' => $name, 'email' => $email, 'message' => $message], function($message)
            {
                $message->to('orders@astore.tj', 'aStore')->subject('Форма обратной связи');
                $message->from('noreply.me8787@gmail.com', 'aStore');
            });*/

            return response()->json(array('sts' => 'ok', 'dannie' => $input), 200);
        }
    }


}
