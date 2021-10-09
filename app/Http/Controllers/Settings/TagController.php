<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\Tag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
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
        $search_name = request()->input('search_name');
        $search_nameMm = request()->input('search_nameMm');

        if ($search_name) {
            $search_name_query = ['name', $search_name];
        } else {
            $search_name_query = ['name', '!=', NULL];
        }
        if ($search_nameMm) {
            $search_nameMm_query = ['nameMm', $search_nameMm];
        } else {
            $search_nameMm_query = ['nameMm', '!=', NULL];
        }

        $tags = Tag::where([
            $search_name_query,
            $search_nameMm_query,
        ])->paginate(10);
        $default = [
            'title' => 'Tags (tags) List',
            'tags' => $tags,
            'search_name' => $search_name,
            'search_nameMm' => $search_nameMm,
        ];

        return view('settings.tags.index', $default)->with('i', (request()->input('page', 1) - 1) * 10);
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
        return view('settings.tags.create', $default);
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
            'name' => 'unique:tags|max:255',
            'nameMm' => 'required|unique:tags|max:255',
        ]);

        $tag = new Tag;
        $tag->name = Str::lower($request->input('name'));
        $tag->nameMm = $request->input('nameMm');
        $tag->save();

        return redirect(route('tag.index'))->with('success', 'Successfully Created!');
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
            'tag' => Tag::find($id)
        ];

        return view('settings.tags.create', $default);
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
            'nameMm' => 'required|max:255',
        ]);

        $tag = Tag::find($id);
        $tag->name = Str::lower($request->input('name'));
        $tag->nameMm = $request->input('nameMm');
        $tag->save();

        return redirect(route('tag.index'))->with('success', 'Successfully Edited!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tag = Tag::find($id);
        $tag->delete();

        return back()->with('success', 'Successfully Deleted!');
    }
}
