<?php

namespace App\Http\Controllers\Site;

use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::where('is_active', 1)->orderBy('date','desc')->paginate(5); // get with limit
        return view('site.news.index', compact('news'));
    }

    public function detail($alias)
    {
        $data = News::where('alias', $alias)->first();

        $str = $data->content;
        $str = str_replace('[youtube]','<div class="row"><div class="col-md-6"><div class="videoWrapper"><iframe width="560" height="349" src="http://www.youtube.com/embed/',$str);
        $str = str_replace('[/youtube]','?rel=0&hd=1" frameborder="0" allowfullscreen></iframe></div></div></div>',$str);

        $data->content = $str;

        if($data != ''){
            return view('site.news.detail', compact('data'));
        }
        else{
            return view('errors.404');
        }

    }

}
