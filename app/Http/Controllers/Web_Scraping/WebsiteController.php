<?php

namespace App\Http\Controllers\Web_Scraping;

use App\Models\Scrapes\Website;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebsiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search_name = request()->input('search_name');

        if ($search_name) {
            $search_name_query = ['name', $search_name];
        } else {
            $search_name_query = ['name', '!=', NULL];
        }

        $websites = Website::where([
            $search_name_query,
        ])->paginate(10);
        $default = [
            'title' => 'Provider\'s Source',
            'websites' => $websites,
            'search_name' => $search_name,
        ];

        return view('web_scraping.websites.index', $default)->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $default = [
            'title' => 'Create Provider\'s Source',
        ];
        return view('web_scraping.websites.create', $default);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:websites|max:255',
            'url' => 'required|unique:websites|max:255',
            // 'host' => 'required',
            'provider_category' => 'required|unique:websites|max:255',
        ]);

        $website = new Website;
        $website->name = Str::lower($request->input('name'));
        $website->url = Str::lower($request->input('url'));
        $website->provider_category = Str::lower($request->input('provider_category'));
        $website->host = $request->input('host');
        $website->save();

        return redirect(route('website.index'))->with('success', 'Successfully Created!');
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
        $default = [
            'title' => 'Edit Provider\'s Source',
            'website' => Website::find($id)
        ];

        return view('web_scraping.websites.create', $default);
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
        $validated = $request->validate([
            'name' => 'required|max:255',
            'url' => 'required|max:255',
        ]);

        $website = Website::find($id);
        $website->name = Str::lower($request->input('name'));
        $website->url = Str::lower($request->input('url'));
        $website->provider_category = Str::lower($request->input('provider_category'));
        $website->host = $request->input('host');
        $website->save();

        return redirect(route('website.index'))->with('success', 'Successfully Edited!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $website = Website::find($id);
        $website->delete();

        return back()->with('success', 'Successfully Deleted!');
    }
}
