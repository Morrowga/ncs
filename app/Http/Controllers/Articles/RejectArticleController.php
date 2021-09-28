<?php

namespace App\Http\Controllers\Articles;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Articles\RawArticle;
use App\Models\Scrapes\Content;
use Illuminate\Http\Request;


class RejectArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $search_type = request()->input('search_type'); // 1 = ID, 2 = UUID, 3 = Title, 4 = Pub:Date
        $search_data = request()->input('search_data');

        if ($search_type == 1) {
            $searching = ['id', $search_data];
        } elseif ($search_type == 2) {
            $searching = ['uuid', $search_data];
        } elseif ($search_type == 3) {
            $searching = ['title', 'LIKE', "%$search_data%"];
        } elseif ($search_type == 4) {
            $searching = ['published_date', date('Y-m-d H:i:s', strtotime($search_data))];
        } else {
            $searching = ['id', '!=', NULL];
        }

        $reject_articles = RawArticle::with('website', 'category', 'tags')
            ->where([
                ['status', '>', 1],
                // ['status', '=', '3'],
                $searching
            ])
            ->orderByDesc('published_date');
        $default = [
            'reject_articles' => $reject_articles->paginate(15),
            'search_type' => $search_type,
            'search_data' => $search_data
        ];


        return view('articles.reject_articles.index', $default)->with('i', (request()->input('page', 1) - 1) * 15);;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $raws = RawArticle::with('category', 'website', 'tags')->find($id);
        $contents = Content::where('article_id', $raws->id)->get();
        $default = [
            'title' => 'Raw Article Detail',
            'raws' => $raws->find($id),
        ];
        // dd($contents);
        return view('articles.raw_articles.detail', $default)->with('contents', $contents);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
