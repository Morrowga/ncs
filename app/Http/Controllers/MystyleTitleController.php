<?php

namespace App\Http\Controllers;

use App\MystyleTitle;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class MystyleTitleController extends Controller
{
    //
    public function index(Request $request)
    {
        if(!empty($request->date)){
            $date = $request->get('date');
        }else
        $date = date('Y-m-d');
        $mystyle_title = MystyleTitle::orderBy("created_at", 'desc')
        ->whereDate('created_at', '=', $date)
        ->paginate(8);
        // ->withPath('?search=' . $search);
        // dd($mystyle_title);
        return view('mystyle_title.index',compact('mystyle_title'));
    }
}
