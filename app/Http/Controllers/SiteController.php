<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;
use App\Noticia;
use App\Pagina;
use App\Licitacao;
use App\HomeImagem;

class SiteController extends Controller
{
    public function index()
    {
        $noticias = Noticia::where('publicada','Sim')
            ->whereNull('idregional')
            ->whereNull('categoria')
            ->whereNull('idcurso')
            ->orderBy('created_at','DESC')
            ->limit(3)
            ->get();
        $cotidianos = Noticia::where('publicada','Sim')
            ->where('categoria','Cotidiano')
            ->orderBy('created_at','DESC')
            ->limit(3)
            ->get();
        $imagens = HomeImagem::select('ordem','url','url_mobile','link','target')
            ->orderBy('ordem','ASC')
            ->get();
        return response()
            ->view('site.home', compact('noticias','cotidianos','imagens'))
            ->header('Cache-Control','no-cache');
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

        if(isset($busca)) {
            $resultados = collect();
            $paginas = Pagina::select('titulo','slug','created_at','conteudo')
                ->where('titulo','LIKE','%'.$busca.'%')
                ->orWhere('conteudo','LIKE','%'.$busca.'%')
                ->limit(10)
                ->get();
            $noticias = Noticia::select('titulo','slug','created_at','conteudo')
                ->where('titulo','LIKE','%'.$busca.'%')
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
        } else {
            return redirect()->route('site.home');
        }
    }

    public function feiras()
    {
        $noticias = Noticia::select('img','slug','titulo','created_at','conteudo')
            ->orderBy('created_at', 'DESC')
            ->where('publicada','Sim')
            ->where('categoria','Feiras')
            ->paginate(9);
        return view('site.feiras', compact('noticias'));
    }
}
