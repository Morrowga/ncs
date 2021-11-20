<?php

namespace App\Http\Controllers\Articles;

use App\Models\Articles\RawArticle;
use App\Models\Scrapes\Website;
use App\Models\Settings\Tag;
use App\Models\Settings\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Scrapes\Content;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Log;
use PHPUnit\TextUI\Help;

class RawArticleController extends Controller
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
        $websites = Website::get();
        if ($search_type == 1) {
            $searching = ['id', $search_data];
        } elseif ($search_type == 2) {
            $searching = ['uuid', $search_data];
        } elseif ($search_type == 3) {
            $searching = ['title', 'LIKE', '%' . $search_data . '%'];
        } elseif ($search_type == 4) {
            $searching = ['publishedDate', '>=', date('Y-m-d H:i:s', strtotime($search_data))];
        } elseif ($search_type == 5) {
            $searching = ['website_id', $search_data];
        } else {
            $searching = ['id', '!=', NULL];
        }
        $categories = Category::get();
        $tags = Tag::get();
        $raw_articles = RawArticle::with('category', 'website', 'tags')
            ->where([
                ['sent_status', 0],
                $searching
            ])
            ->orderByDesc('id');

        // insert uuid
        // foreach ($raw_articles->get() as $raw) {
        //     if (empty($raw->uuid)) {
        //         $data = RawArticle::find($raw->id);
        //         $data->uuid = Helper::uuid();
        //         $data->save();
        //     }
        // }

        $default = [
            'raw_articles' => $raw_articles->paginate(15),
            'search_type' => $search_type,
            'search_data' => $search_data,
        ];
        // dd($default);
        return view('articles.raw_articles.index', $default)->with('i', (request()->input('page', 1) - 1) * 15);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $default = [
            'title' => 'Create Raw Article',
            'websites' => Website::get(),
            'categories' => Category::get(),
            'tags' => Tag::get(),
        ];
        return view('articles.raw_articles.create', $default);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $raws = new RawArticle;
        $raws->uuid = Helper::uuid();
        $raws->source_link = $request->source_link;
        $raws->website_id = $request->website_id;
        $raws->category_id = $request->category_id;
        $raws->publishedDate = date('Y-m-d H:i:s', strtotime($request->publishedDate));

        $raws->title = tounicode($request->title);
        //image
        $raws->image = $request->image;
        $raws->content = tounicode($request->text_area);

        $raws->save();
        //tags
        $raws->tags()->sync((array)$request->input('tag'));
        return redirect()->route('raw_articles.index')->with('success', 'Successfully Created!');
        // dd($request->all());
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
        $sensitive = Helper::sensitive_keywords($id);
        // $content_count = Helper::no_content($id);
        $duplicate_title = Helper::duplicate_with_title($id);
        $duplicate_content = Helper::duplicate_with_content($id);

        $default = [
            'title' => 'Raw Article Detail',
            'raws' => $raws->find($id),
            'blacklist' => $blacklist,
            'sensitive' => $sensitive,
            'duplicate_title' => $duplicate_title,
            // 'duplicate_content' => $duplicate_content
            // 'content_count' => $content_count
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
        $suggest_category = Helper::suggest_category($id);
        $suggest_indexing = Helper::indexing_category($id);
        $suggest_website = Helper::suggest_website($id);
        // $indexing_tags = Helper::indexing_tags($id);
        // $test =  Helper::categorywith_title($id);


        $default = [
            'title' => 'Edit Raw Article',
            'raws' => $raws,
            // 'suggest' => $suggesting_tags
        ];
        return view('articles.raw_articles.edit', $default, compact('categories', 'websites', 'tags', 'contents', 'suggesting_tags', 'suggest_category', 'suggest_indexing', 'suggest_website'));
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
        $raws = RawArticle::with('category', 'website', 'tags')->findorFail($id);
        $raws->uuid = Helper::uuid();
        $raws->source_link = $request->source_link;
        $raws->website_id = $request->website_id;
        $raws->category_id = $request->category_id;
        $raws->publishedDate = date('Y-m-d H:i:s', strtotime($request->publishedDate));

        $raws->title = $request->title;
        //image
        $raws->image = $request->image;
        $raws->content = $request->text_area;

        $raws->update();
        //tags
        $raws->tags()->sync((array)$request->input('tag'));
        return redirect()->route('raw_articles.index')->with('success', 'Successfully Edited!');
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
        $raws = RawArticle::findorFail($id);
        $raws->delete();
        return back()->with('success', 'Successfully Deleted!');
    }

    //sent_lotaya
    public function sent_lotaya($id)
    {
        $raw_articles = RawArticle::with('website', 'category', 'tags')->findorFail($id);
        $raw_articles->sent_status = 1;
        $raw_articles->save();
        return redirect()->route('sent_articles.index', compact('raw_articles'))->with('success', 'Successfully Send!');
    }
    // duplicate
    public function duplicate($id)
    {
        $raw_articles = RawArticle::with('website', 'category', 'tags')->findorFail($id);
        $raw_articles->sent_status = 3;
        $raw_articles->save();
        return redirect()->route('raw_articles.index', compact('raw_articles'))->with('success', 'Successfully Duplicate!');
    }
    // blacklist
    public function blacklist($id)
    {
        $raw_articles = RawArticle::with('website', 'category', 'tags')->findOrFail($id);
        $raw_articles->sent_status = 2;
        $raw_articles->save();
        return redirect()->route('raw_articles.index', compact('raw_articles'))->with('success', 'Successfully Blacklist!');
    }
    public function laravelLog(Request $request)
    {
        $date =  new Carbon($request->get('date', today()));
        $data = [];
        $filePath = storage_path() . '/logs/laravel-' . $date->format('Y-m-d') . '.log';
        // dd($filePath);
        if (File::exists($filePath)) {
            $data = File::get($filePath);
        }

        return view('laravellog', compact('data', 'date'));
    }
    public function activityLog(Request $request)
    {
        $search =  $request->input('q');
        if ($search != "") {
            $logs = Log::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('created_at', 'like', '%' . $search . '%');
            })
                ->paginate(10);
            $logs->appends(['q' => $search]);
        } else {
            $startDate = Carbon::createFromFormat('d/m/Y', '01/10/2021');
            $endDate = Carbon::now();
            $logs = Log::orderBy('created_at', 'DESC')->whereBetween('created_at', [$startDate, $endDate])->paginate(10);
        }
        return view('activitylog', compact('logs'));
    }
}
