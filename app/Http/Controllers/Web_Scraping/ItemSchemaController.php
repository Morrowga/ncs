<?php

namespace App\Http\Controllers\Web_Scraping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scrapes\ItemSchema;

class ItemSchemaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search_title = request()->input('search_title');

        if ($search_title) {
            $search_title_query = ['title', $search_title];
        } else {
            $search_title_query = ['title', '!=', NULL];
        }
        $itemschema = ItemSchema::where([
            $search_title_query,
        ])->paginate(10);

        $default = [
            'title' => 'Item Schema List',
            'itemschema' => $itemschema,
            'search_title' => $search_title,
        ];
        return view('web_scraping.itemschema.index', $default);
    }


    public function itemSchemaData()
    {
        $itemSchemas = ItemSchema::get();
        return view('web_scraping.itemschemas.index', compact('itemSchemas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $default = [
            'title' => 'Create Item Schema',
        ];
        return view('web_scraping.itemschema.create', $default);
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
        $itemschema = new ItemSchema;
        $itemschema->title = $request->input('title');
        if ($request->input('is_full_url') != null) {
            $itemschema->is_full_url = 1;
        } else {
            $itemschema->is_full_url = 0;
        }
        $itemschema->css_expression = $request->input('css_expression');
        $itemschema->full_content_selector = $request->input('full_content_selector');
        $itemschema->save();
        return redirect()->route('itemschema.index');
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
        $default = [
            'title' => 'Edit Item Schema',
            'itemschema' => ItemSchema::find($id)
        ];
        return view('web_scraping.itemschema.edit', $default);
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
        $itemschema = ItemSchema::find($id);
        $itemschema->title = $request->input('title');
        if ($request->input('is_full_url') != null) {
            $itemschema->is_full_url = 1;
        } else {
            $itemschema->is_full_url = 0;
        }
        $itemschema->css_expression = $request->input('css_expression');
        $itemschema->full_content_selector = $request->input('full_content_selector');
        $itemschema->save();
        return redirect()->route('itemschema.index');
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
        $itemschema = ItemSchema::findOrFail($id);
        $itemschema->delete();

        return back()->with('success', 'Successfully Deleted!');
    }
}
