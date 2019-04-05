<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Regional;

class RegionalSiteController extends Controller
{
    public function regionaisView()
    {
        $regionais = Regional::all();
        return view('site.regionais', compact('regionais'));
    }
}
