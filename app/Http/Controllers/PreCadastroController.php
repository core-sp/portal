<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreCadastroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['create']]);
    }

    public function index()
    {
        
    }

    public function create()
    {
        return view('site.pre-cadastro');
    }

    public function store()
    {
        
    }

    public function edit()
    {
        
    }

    public function update()
    {
        
    }
}
