<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
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

        $categories = Category::where([
            $search_name_query,
            $search_nameMm_query,
        ])->paginate(10);
        $default = [
            'title' => 'Categories List',
            'categories' => $categories,
            'search_name' => $search_name,
            'search_nameMm' => $search_nameMm,
        ];

        return view('settings.categories.index', $default)->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $default = [
            'title' => 'Create Category',
        ];
        return view('settings.categories.create', $default);
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
            'id_name' => 'nullable',
            'name' => 'required|unique:categories|max:255',
            'nameMm' => 'required|unique:categories|max:255',
        ]);

        $category = new Category;
        $category->id_name = Str::lower($request->input('name'));
        $category->name = Str::lower($request->input('name'));
        $category->nameMm = $request->input('nameMm');
        $category->save();

        return redirect(route('category.index'))->with('success', 'Successfully Created!');
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
            'title' => 'Edit Category',
            'category' => Category::find($id)
        ];

        return view('settings.categories.create', $default);
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
            'id_name' => 'nullable',
            'name' => 'required|max:255',
            'nameMm' => 'required|max:255',
        ]);

        $category = Category::find($id);
        $category->id_name = Str::lower($request->input('name'));
        $category->name = Str::lower($request->input('name'));
        $category->nameMm = $request->input('nameMm');
        $category->save();

        return redirect(route('category.index'))->with('success', 'Successfully Edited!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        $category->delete();

        return back()->with('success', 'Successfully Deleted!');
    }
}
