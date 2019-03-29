<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BdoOportunidade;

class BdoSite extends Controller
{
    public function index()
    {
    	$oportunidades = BdoOportunidade::paginate(10);
    	return view('site.balcao-de-oportunidades');
    }
}
