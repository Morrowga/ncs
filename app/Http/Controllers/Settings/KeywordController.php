<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\Keyword;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KeywordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search_name = request()->input('search_name');
        $search_name_mm = request()->input('search_name_mm');

        if ($search_name) {
            $search_name_query = ['name', $search_name];
        } else {
            $search_name_query = ['name', '!=', NULL];
        }
        if ($search_name_mm) {
            $search_name_mm_query = ['name_mm', $search_name_mm];
        } else {
            $search_name_mm_query = ['name_mm', '!=', NULL];
        }

        $keywords = Keyword::where([
            $search_name_query,
            $search_name_mm_query,
        ])->paginate(10);
        $default = [
            'title' => 'Tags (Keywords) List',
            'keywords' => $keywords,
            'search_name' => $search_name,
            'search_name_mm' => $search_name_mm,
        ];

        return view('settings.keywords.index', $default)->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $default = [
            'title' => 'Create Tag',
        ];
        return view('settings.keywords.create', $default);
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
            'name' => 'unique:keywords|max:255',
            'name_mm' => 'required|unique:keywords|max:255',
        ]);

        $category = new Keyword;
        $category->name = Str::lower($request->input('name'));
        $category->name_mm = $request->input('name_mm');
        $category->save();

        return redirect(route('keyword.index'))->with('success', 'Successfully Created!');
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
            'title' => 'Edit Tag',
            'keyword' => Keyword::find($id)
        ];

        return view('settings.keywords.create', $default);
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
            'name' => 'max:255',
            'name_mm' => 'required|max:255',
        ]);

        $keyword = Keyword::find($id);
        $keyword->name = Str::lower($request->input('name'));
        $keyword->name_mm = $request->input('name_mm');
        $keyword->save();

        return redirect(route('keyword.index'))->with('success', 'Successfully Edited!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $keyword = Keyword::find($id);
        $keyword->delete();

        return back()->with('success', 'Successfully Deleted!');
    }
}
