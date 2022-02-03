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
use App\Models\Scrapes\Content;
use App\Models\Scrapes\Website;
use App\Models\Settings\Category;
use App\Models\Settings\Tag;
use App\Helpers\Helper;


class SentArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
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
            $searching = ['publishedDate', date('Y-m-d H:i:s', strtotime($search_data))];
        } else {
            $searching = ['id', '!=', NULL];
        }

        $sent_articles = RawArticle::with('category', 'website', 'tags')
            ->where([
                ['sent_status', 1],
                $searching
            ])
            ->orderByDesc('publishedDate');


        $yearly_report = RawArticle::with('category', 'website')
            ->where('sent_status', 1)
            ->select(
                'id',
                'title',
                'publishedDate',
                'created_at'
            )
            ->whereYear('publishedDate', Carbon::now()->year)
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
        $raws = RawArticle::with('category', 'website', 'tags')->find($id);
        $contents = Content::where('article_id', $raws->id)->get();
        // $check_duplicate = Helper::checkDuplicate($id);
        $blacklist = Helper::checkBlacklist($id);
        $default = [
            'title' => 'Raw Article Detail',
            'raws' => $raws->find($id),
            'blacklist' => $blacklist,
            // 'check_duplicate' => $check_duplicate
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
        $raws = RawArticle::with('website', 'category', 'tags')->find($id);
        $categories = Category::get();
        $websites = Website::get();
        $tags = Tag::get();

        $contents = Content::where('article_id', $raws->id)->get();
        $suggesting_tags = Helper::suggest_tags($id);

        $default = [
            'title' => 'Edit Raw Article',
            'raws' => $raws,
            // 'suggest' => $suggesting_tags
        ];
        return view('articles.raw_articles.edit', $default, compact('categories', 'websites', 'tags', 'contents', 'suggesting_tags'));
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
        $month = 2;
        $monthly_report = RawArticle::with('category', 'website')
            ->where('sent_status', 1)
            ->whereMonth('created_at', $month)
            ->paginate(10);
        // dd($monthly_report);

        return view('report.monthly', compact('monthly_report'))->with('i', (request()->input('page', 1) - 1) * 15);
    }
    public function export()
    {
        return Excel::download(new SentArticlesExport, 'invoices.xlsx');
    }
    //excel category
    public function excel_category(Request $request)
    {
        $excel_category = $request->input('excel_pro_category');
        $id = $request->input('id');
        RawArticle::where('id', $id)->update(array(
            'excel_pro_category' => $excel_category,
        ));
        // return redirect()->route('monthly');
        return redirect()->back();

        // dd($excel_category, $id);
    }
}
