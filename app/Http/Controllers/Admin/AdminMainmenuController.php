<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminMainmenuRequest;
use App\Models\Mainmenu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminMainmenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mainmenu = Mainmenu::orderBy('type','asc')->orderBy('position','asc')->get();
        return view('admin.mainmenu.index', compact('mainmenu'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.mainmenu.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminMainmenuRequest $request)
    {
        $input = $request->all();
        Mainmenu::create($input);
        return redirect('/admin/mainmenu')->with(['success_message' => 'Успешно!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $mainmenu = Mainmenu::findOrFail($id);
        return view('admin.mainmenu.show', compact('mainmenu'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $mainmenu = Mainmenu::findOrFail($id);
        return view('admin.mainmenu.edit', compact('mainmenu'));
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
        $mainmenu = Mainmenu::findOrFail($id);

        $messages = [
            'position.required' => 'Введите позицию',
            'title.required' => 'Введите заголовок',
            'alias.required' => 'Введите алиас',
            'alias.unique' => 'Алиас должен быть уникальным',
        ];

        $this->validate($request, [
            'position' => 'required',
            'title' => 'required',
            'alias' => 'required|unique:mainmenus,alias,' . $mainmenu->id,
        ],$messages);

        $mainmenu->update($input);
        return redirect('admin/mainmenu')->with(['success_message' => 'Сохранена!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $mainmenu = Mainmenu::findOrFail($id);
        $mainmenu->delete();
        return redirect('/admin/mainmenu')->with(['success_message' => 'Удалена!']);
    }
}
