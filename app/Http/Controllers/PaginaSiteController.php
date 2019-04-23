<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Pagina;

class PaginaSiteController extends Controller
{
    public function show($categoria, $slug)
    {
        $pagina = Pagina::where('slug', $slug)->first();
        if(isset($pagina)) {
            $slug = Str::slug($pagina->paginacategoria->nome, '-');
            if ($categoria == $slug) {
                return view('site.pagina', compact('pagina', 'categoria'));
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
        
    }

    public function showSemCategoria($slug)
    {
        $pagina = Pagina::where('slug', $slug)->first();
        if(isset($pagina)) {
            if (!isset($pagina->paginacategoria->nome)) {
                return view('site.pagina', compact('pagina'));
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }
}
