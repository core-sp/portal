<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Noticia;
use App\Pagina;
use App\Licitacao;

class SiteController extends Controller
{
    public function index()
    {	
    	$noticias = Noticia::limit(3)->get();
    	return view('site.home', compact('noticias'));
    }

    public function busca(Request $request)
    {
        $busca = Input::get('busca');
        $regras = [
            'busca' => 'required|min:3',
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório',
            'min' => 'Insira no mínimo três caracteres'
        ];
        $erros = $request->validate($regras, $mensagens);

        $resultados = collect();
        $paginas = Pagina::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->limit(10)
            ->get();
        $noticias = Noticia::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->limit(10)
            ->get();
        foreach($paginas as $pagina) {
            $pagina->tipo = "Página";
            $resultados->push($pagina);
        }
        foreach($noticias as $noticia) {
            $noticia->tipo = "Notícia";
            $resultados->push($noticia);
        }
        return view('site.busca', compact('busca', 'resultados'));
    }
}
