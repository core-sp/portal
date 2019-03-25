<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Noticia;

class SiteController extends Controller
{
    public function index()
    {	
    	$noticias = Noticia::limit(3)->get();
    	return view('site.home', compact('noticias'));
    }
}
