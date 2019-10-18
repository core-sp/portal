<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RepresentanteSiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:representante')->except(['cadastroView', 'cadastro']);
    }

    public function index()
    {
        return view('site.representante.home');
    }

    public function cadastroView()
    {
        return view('site.representante.cadastro');
    }
}
