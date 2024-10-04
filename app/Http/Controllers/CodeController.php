<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodeController extends Controller
{
    public function getAdCode()
    {
        //get random code
        $code = DB::table('code_ads')->inRandomOrder()->first();

        return response()->json([
            'success' => true,
            'data' => $code
        ]);
    }
}
