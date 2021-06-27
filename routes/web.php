<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Artisan;


Route::post('/webhooks/handle', 'WebhookController@handle');




// CACHE
//Clear Cache facade value:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('route:cache');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('dump-autoload');
    return '<h1>Cache facade value cleared</h1>';
});

//Route cache:
Route::get('/route-cache', function() {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});

Route::get('/updateapp', function()
{
    $exitCode = Artisan::call('dump-autoload');
    return '<h1>Dump-autoload complete</h1>';
});

Auth::routes();
Route::get('/logout','Auth\AuthController@logout');

Route::get('/admin', function () {
    if(Auth::guest()){
        return redirect('/login');
    }
    else{
        $roleID = Auth::user()->role_id;
        switch ($roleID){
            case 1:
                return redirect('/');
                break;
            case 2:
                return redirect('/admin');
                break;
        }
    }
});

Route::get('/login', array('as' => 'login', function () {
    if(Auth::guest()){
        return view('auth.login');
    }
    else{
        return redirect('/');
    }
}));

/////////////////////////////////////////////////////////////////////////////////
// SITE SIDE
/////////////////////////////////////////////////////////////////////////////////

Route::get('/', 'Site\IndexController@homepage');

Route::get('/rules', function () {
    return view('site.rules');
});

Route::get('/agreement', function () {
    return view('site.agreement');
});

// OAuth Routes
Route::get('auth/{provider}', 'Auth\AuthController@redirectToProvider');
Route::get('auth/{provider}/callback', 'Auth\AuthController@handleProviderCallback');

Route::get('/user', 'Site\UserController@index');
Route::get('/user/profile', 'Site\UserController@profile');
Route::post('/user/profile', 'Site\UserController@profileUpdate');
Route::get('/user/password', 'Site\UserController@password');
Route::post('/user/password', 'Site\UserController@passwordUpdate');
Route::get('/user/orders', 'Site\UserController@orders');
Route::get('/user/orders/{id}', 'Site\UserController@viewOrder');

Route::post('/addtocart', 'Site\IndexController@addToCart');
Route::get('/showcart', 'Site\IndexController@showCart');
Route::post('/changecartinpt', 'Site\IndexController@changeCartInput');
Route::post('/removeprodfromcart', 'Site\IndexController@removeProductFromCart');
Route::get('/refreshcartontop', 'Site\IndexController@refreshCartOnTop');
Route::get('/order', 'Site\IndexController@orderCart');
Route::post('/makeorder', 'Site\IndexController@makeOrder');

Route::get('/news', 'Site\NewsController@index');
Route::get('news/{alias}', 'Site\NewsController@detail')->name('news_alias');

//Route::get('category/', 'Site\CategoryController@categoryList');
Route::get('/category/{alias}', 'Site\CategoryController@categoryProducts')->name('category_alias');

Route::get('/products', 'Site\ProductController@productList');
Route::get('/products/{alias}', 'Site\ProductController@productDetail')->name('product_alias');
Route::post('/products/reviews', 'Site\ProductController@productReviews');
Route::post('/products/filter', 'Site\ProductController@filter');

Route::get('/{alias}', 'Site\IndexController@pageDetail')->name('page_alias');
Route::get('/search/{query}', 'Site\IndexController@searchResult');

Route::post('/sendmessage', 'Site\IndexController@sendMsg');
Route::post('/predzakaz', 'Site\ProductController@preorder');
Route::post('/predzakaz/do', 'Site\ProductController@makePreorder');

/////////////////////////////////////////////////////////////////////////////////
// ADMIN PANEL
/////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware'=> ['auth','admin']], function () {

    Route::get('/admin/laravel-filemanager', '\UniSharp\LaravelFilemanager\Controllers\LfmController@show');
    Route::post('/admin/laravel-filemanager/upload', '\UniSharp\LaravelFilemanager\Controllers\UploadController@upload');

    Route::get('/admin', function () { return view('admin.index'); });
    Route::resource('/admin/mainmenu','Admin\AdminMainmenuController');
    Route::resource('/admin/infomenu','Admin\AdminInfomenuController');

    Route::get('/admin/category/deleteimg/{id}', 'Admin\AdminCategoriesController@deleteimg');
    Route::resource('/admin/category','Admin\AdminCategoriesController');

    Route::get('/admin/products/category/{id}/', 'Admin\AdminProductsController@productsByCategory');
    Route::get('/admin/products/deleteimg/{id}', 'Admin\AdminProductsController@deleteimg');
    Route::post('/admin/products/gallery', 'Admin\AdminProductsController@ajaxUploadImage');
    Route::post('/admin/products/gallery/remove', 'Admin\AdminProductsController@ajaxRemoveImage');
    Route::resource('/admin/products','Admin\AdminProductsController');

    Route::get('/admin/slideshow/deleteimg/{id}', 'Admin\AdminSlideshowController@deleteimg');
    Route::resource('/admin/slideshow','Admin\AdminSlideshowController');
    Route::get('/admin/saleblock/deleteimg/{id}', 'Admin\AdminSaleblockController@deleteimg');
    Route::resource('/admin/saleblock','Admin\AdminSaleblockController');
    Route::get('/admin/news/deleteimg/{id}', 'Admin\AdminNewsController@deleteimg');
    Route::resource('/admin/news','Admin\AdminNewsController');
    Route::get('/admin/partners/deleteimg/{id}', 'Admin\AdminPartnerController@deleteimg');
    Route::resource('/admin/partners','Admin\AdminPartnerController');
    Route::resource('/admin/settings','Admin\AdminSettingsController');
    Route::resource('/admin/users', 'Admin\AdminUsersController');
    Route::resource('/admin/orders','Admin\AdminOrderController');
    Route::resource('/admin/preorders','Admin\AdminPreorderController');

//    Route::namespace('Webhooks')->prefix('/webhooks')->group(function () {

//    });
});