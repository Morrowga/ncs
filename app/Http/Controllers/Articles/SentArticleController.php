<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Models\Articles\RawArticle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Sheet;
use App\Exports\SentArticlesExport;
use App\Helpers\Helper;
use App\Models\Scrapes\Content;

class SentArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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

        $sent_articles = RawArticle::with('category', 'website', 'tags')
            ->where([
                ['status', 1],
                $searching
            ])
            ->orderByDesc('created_at');


        $yearly_report = RawArticle::with('category', 'website')
            ->where('status', 1)
            ->select(
                'id',
                'title',
                'published_date',
                'created_at'
            )
            ->whereYear('published_date', Carbon::now()->year)
            ->get();
        // dd($yearly_report);

        $default = [
            'sent_articles' => $sent_articles->paginate(10),
            'search_type' => $search_type,
            'search_data' => $search_data,
        ];
        // dd($default);
        return view('articles.sent_articles.index', $default)->with('i', (request()->input('page', 1) - 1) * 15);
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
    // monthly report
    public function monthly(Request $request)
    {
        // $monthly_report = RawArticle::with('category', 'website')
        //     ->where('status', 1)
        //     ->whereMonth('published_date', Carbon::now()->month)
        //     ->paginate(10);
        // dd($monthly_report);

        $month = 9;
        $monthly_report = RawArticle::with('category', 'website')
            ->where('status', 1)
            ->whereMonth('published_date', $month)
            ->paginate(10);

        return view('report.monthly', compact('monthly_report'))->with('i', (request()->input('page', 1) - 1) * 15);
    }
    public function export()
    {
        return Excel::download(new SentArticlesExport, 'invoices.xlsx');
    }
}
