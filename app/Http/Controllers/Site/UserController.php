<?php

namespace App\Http\Controllers\Site;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::user()->id);
        return view('site.user.index', compact('user'));
    }

    public function profile()
    {
        $user = User::findOrFail(Auth::user()->id);
        return view('site.user.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        $input = $request->all();
        //dd($input);

        $messages = [
            'name.required' => 'Введите имя',
            //'email.required' => 'Введите эл. почту',
            //'email.email' => 'Введите правильную эл. почту',
            //'email.unique' => 'Эл. почта занята. Выберите другую эл. почту.',
            'phone.required' => 'Введите телефон',
            'address.required' => 'Введите адрес',
        ];

        $this->validate($request, [
            'name' => 'required',
            //'email' => 'required|email|unique:users,email,' . $input['email'],
            'phone' => 'required',
            'address' => 'required',
        ],$messages);

        $user->name = $input['name'];
        $user->phone = $input['phone'];
        $user->address = $input['address'];
        $user->save();

        return redirect('/user/profile')->with('success_message','Успешно сохранен!');
    }

    public function password()
    {
        return view('site.user.password');
    }

    public function passwordUpdate(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        $input = $request->all();

        $messages = [
            'password.required' => 'Введите пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль должен быть не менее 6 символов',
        ];

        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
        ],$messages);

        $user->password = bcrypt($request->password);
        $user->save();
        return redirect('/user/password')->with('success_message','Успешно сохранен!');
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::user()->id)->orderBy('order_date','desc')->paginate(15);
        return view('site.user.orders', compact('orders'));
    }

    public function viewOrder($id)
    {
        $order = Order::where(['user_id' => Auth::user()->id, 'id' => $id])->first();
        if(!empty($order)){
            return view('site.user.ordersView', compact('order'));
        }
        else{
            return redirect('/user/orders');
        }

    }

}
