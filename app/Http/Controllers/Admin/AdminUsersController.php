<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminUsersRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name', 'id');
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminUsersRequest $request)
    {
        $input = $request->all();
        $input['password'] = bcrypt($request->password);
        User::create($input);
        return redirect('/admin/users')->with('success_message','Успешно добавлен!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $roles = Role::pluck('name', 'id');
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact(['roles','user']));
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
        $user = User::findOrFail($id);
        $input = $request->all();

        $messages = [
            'name.required' => 'Введите имя',
            'role_id.required' => 'Выберите роль',
            'email.required' => 'Введите эл. почту',
            'email.email' => 'Введите правильную эл. почту',
            'email.unique' => 'Эл. почта занята. Выберите другую эл. почту.',
        ];

        $this->validate($request, [
            'name' => 'required',
            'role_id' => 'required',
            'email' => 'required|email|unique:users,email,' .$user->id,
        ],$messages);


        if(trim($request->password) != ''){

            $messages = [
                'password.required' => 'Введите пароль',
                'password.confirmed' => 'Пароли не совпадают',
                'password.min' => 'Пароль должен быть не менее 6 символов',
            ];

            $this->validate($request, [
                'password' => 'required|confirmed|min:6',
            ],$messages);

            $input['password'] = bcrypt($request->password);

        }
        else{
            $input = $request->except('password');
        }
        if(empty($input['is_active'])){ $input['is_active'] = '0'; }
        $user->update($input);
        return redirect('/admin/users')->with('success_message','Успешно сохранен!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect('/admin/users')->with(['success_message' => 'Успешно удален!']);
    }


}
