<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
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

        $categories = Category::where([
            $search_name_query,
            $search_name_mm_query,
        ])->paginate(10);
        $default = [
            'title' => 'Categories List',
            'categories' => $categories,
            'search_name' => $search_name,
            'search_name_mm' => $search_name_mm,
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
            'name' => 'required|unique:categories|max:255',
            'name_mm' => 'required|unique:categories|max:255',
        ]);

        $category = new Category;
        $category->name = Str::lower($request->input('name'));
        $category->name_mm = $request->input('name_mm');
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
            'name' => 'required|max:255',
            'name_mm' => 'required|max:255',
        ]);

        $category = Category::find($id);
        $category->name = Str::lower($request->input('name'));
        $category->name_mm = $request->input('name_mm');
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
