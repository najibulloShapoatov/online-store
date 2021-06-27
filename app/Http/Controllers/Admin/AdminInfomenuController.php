<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminInfomenuRequest;
use App\Models\Infomenu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminInfomenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $infomenu = Infomenu::orderBy('position','asc')->get();
        return view('admin.infomenu.index', compact('infomenu'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.infomenu.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminInfomenuRequest $request)
    {
        $input = $request->all();
        Infomenu::create($input);
        return redirect('/admin/infomenu')->with(['success_message' => 'Успешно!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $infomenu = Infomenu::findOrFail($id);
        return view('admin.infomenu.show', compact('infomenu'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $infomenu = Infomenu::findOrFail($id);
        return view('admin.infomenu.edit', compact('infomenu'));
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
        $infomenu = Infomenu::findOrFail($id);

        $messages = [
            'position.required' => 'Введите позицию',
            'title.required' => 'Введите заголовок',
            'alias.required' => 'Введите алиас',
            'alias.unique' => 'Алиас должен быть уникальным',
        ];

        $this->validate($request, [
            'position' => 'required',
            'title' => 'required',
            'alias' => 'required|unique:infomenus,alias,' . $infomenu->id,
        ],$messages);

        $infomenu->update($input);
        return redirect('admin/infomenu')->with(['success_message' => 'Сохранена!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $infomenu = Infomenu::findOrFail($id);
        $infomenu->delete();
        return redirect('/admin/infomenu')->with(['success_message' => 'Удалена!']);
    }
}
