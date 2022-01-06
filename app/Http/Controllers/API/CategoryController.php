<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings\Category;
use App\Helpers\Asonetoke;
use App\Helpers\Helper;

use function App\Helpers\logText;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = Category::orderBy('sorting', 'ASC')->get();
        $cat_d = [];
        foreach ($category as $cat) {
            $cat_data = [
                "id" => $cat['id_name'],
                "name" => $cat['name'],
                "nameMm" => $cat['nameMm']
            ];
            array_push($cat_d, $cat_data);
        }

        $add_array = [
            "success" => true,
            "data" => $cat_d
        ];
        $log = Helper::logText("Category List");

        return response()->json($add_array);
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
}
