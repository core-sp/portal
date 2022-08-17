<?php

namespace App\Http\Controllers;

// use App\Post;
use Exception;
use App\Pagina;
use App\Noticia;
use App\HomeImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Repositories\CompromissoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;
use App\Contracts\MediadorServiceInterface;

class SiteController extends Controller
{
    private $compromissoRepository;
    private $service;

    public function __construct(CompromissoRepository $compromissoRepository, MediadorServiceInterface $service)
    {
        $this->compromissoRepository = $compromissoRepository;
        $this->service = $service;
    }

    public function index()
    {
        $noticias = Noticia::where('publicada','Sim')
            ->whereNull('idregional')
            ->whereNull('categoria')
            ->orderBy('created_at','DESC')
            ->limit(6)
            ->get();
        $cotidianos = Noticia::where('publicada','Sim')
            ->where('categoria','Cotidiano')
            ->orderBy('created_at','DESC')
            ->limit(4)
            ->get();
        $imagens = HomeImagem::select('ordem','url','url_mobile','link','target')
            ->orderBy('ordem','ASC')
            ->get();
        $posts = $this->service->getService('Post')->latest();
        return response()
            ->view('site.home', compact('noticias','cotidianos','imagens', 'posts'))
            ->header('Cache-Control','no-cache');
    }

    public function busca(Request $request)
    {
        $busca = IlluminateRequest::input('busca');

        $regras = [
            'busca' => 'required|min:3',
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório',
            'min' => 'Insira no mínimo três caracteres'
        ];
        $erros = $request->validate($regras, $mensagens);

        $buscaArray = preg_split('/\s+/', $busca, -1);

        if(isset($busca)) {
            $resultados = collect();
            $paginas = Pagina::selectRaw("'Página' as tipo, titulo, subtitulo, slug, created_at,conteudo")
                ->where(function($query) use ($buscaArray) {
                    foreach($buscaArray as $b) {
                        $query->where(function($q) use ($b) {
                            $q->where('titulo','LIKE','%'.$b.'%')
                                ->orWhere('subtitulo','LIKE','%'.$b.'%')
                                ->orWhere('conteudoBusca','LIKE','%'.$b.'%');
                        });
                    }
                })->limit(10);
            $noticias = Noticia::selectRaw("'Notícia' as tipo, titulo, null as subtitulo, slug, created_at, conteudo")
                ->where(function($query) use ($buscaArray) {
                    foreach($buscaArray as $b) {
                        $query->where(function($q) use ($b) {
                            $q->where('titulo','LIKE','%'.$b.'%')
                                ->orWhere('conteudoBusca','LIKE','%'.$b.'%');
                        });
                    }
                })->orderBy('created_at', 'DESC')
                ->limit(10);
            $posts = $this->service->getService('Post')->buscaSite($buscaArray);
            /*Post::selectRaw("'Post' as tipo, titulo, subtitulo, slug, created_at,conteudo")
                ->where(function($query) use ($buscaArray) {
                    foreach($buscaArray as $b) {
                        $query->where(function($q) use ($b) {
                            $q->where('titulo','LIKE','%'.$b.'%')
                                ->orWhere('subtitulo','LIKE','%'.$b.'%')
                                ->orWhere('conteudoBusca','LIKE','%'.$b.'%');
                        });
                    }
                })->orderBy('created_at', 'DESC')
                ->limit(10);*/

            $resultados = $paginas->union($noticias)->union($posts)->get();

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

    public function acoesFiscalizacao()
    {
        $noticias = Noticia::select('img','slug','titulo','created_at','conteudo')
            ->orderBy('created_at', 'DESC')
            ->where('publicada','Sim')
            ->where('categoria','Fiscalização')
            ->paginate(9);
        return view('site.acoes-da-fiscalizacao', compact('noticias'));
    }

    public function espacoContador()
    {
        $noticias = Noticia::select('img','slug','titulo','created_at','conteudo')
            ->orderBy('created_at', 'DESC')
            ->where('publicada','Sim')
            ->where('categoria','Espaço do Contador')
            ->paginate(9);
        return view('site.espaco-do-contador', compact('noticias'));
    }

    public function agendaInstitucional()
    {
        return redirect()->route('agenda-institucional-data', date('d-m-Y'));
    }

    public function agendaInstitucionalByData($data)
    {
        if(!validDate($data, '01-01-1700', 'd-m-Y')) {
            abort(404);
        }

        $resultados = $this->compromissoRepository->getByData(date('Y-m-d', strtotime($data)));

        return view('site.agenda-institucional', compact('resultados', 'data'));
    }
}
