<?php

namespace App\Http\Controllers;

use App\Log;
use App\Models\Articles\RawArticle;
use App\Models\Settings\Category;
use App\Models\Settings\Tag;
use App\Models\Scrapes\Website;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $raw_articles = RawArticle::with('category', 'website', 'tags')
            ->where('sent_status', 0)
            ->orderByDesc('id')
            ->take(5)
            ->get();
        $sent_articles = RawArticle::with('category', 'website', 'tags')
            ->where('sent_status', 1)
            ->orderByDesc('id')
            ->take(5)
            ->get();
        $logs = Log::orderBy('created_at', 'DESC')
            ->take(5)
            ->get();

        return view('home', compact('raw_articles', 'sent_articles', 'logs'));
    }
}
