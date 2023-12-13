<?php

namespace App\Http\Controllers;

use Exception;
use App\Pagina;
use App\HomeImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Repositories\CompromissoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\SiteRequest;

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
        $latestNoticias = $this->service->getService('Noticia')->latest();
        $noticias = $latestNoticias['noticias'];
        $cotidianos = $latestNoticias['cotidianos'];
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
            $paginas = Pagina::selectRaw("'Página' as tipo, titulo, subtitulo, slug, created_at,conteudo")
                ->where(function($query) use ($buscaArray) {
                    foreach($buscaArray as $b) {
                        $query->where(function($q) use ($b) {
                            $q->where('titulo','LIKE','%'.$b.'%')
                                ->orWhere('subtitulo','LIKE','%'.$b.'%')
                                ->orWhere('conteudoBusca','LIKE','%'.$b.'%');
                        });
                    }
                })->limit(10)
                ->get();
            $noticias = $this->service->getService('Noticia')->buscaSite($buscaArray)->get();
            $posts = $this->service->getService('Post')->buscaSite($buscaArray)->get();

            $resultados = collect([$paginas, $noticias, $posts])->collapse();

            return view('site.busca', compact('busca', 'resultados'));
        } else {
            return redirect()->route('site.home');
        }
    }

    public function feiras()
    {
        $noticias = $this->service->getService('Noticia')->latestByCategoria('Feiras');

        return view('site.feiras', compact('noticias'));
    }

    public function acoesFiscalizacao()
    {
        $noticias = $this->service->getService('Noticia')->latestByCategoria('Fiscalização');

        return view('site.acoes-da-fiscalizacao', compact('noticias'));
    }

    public function espacoContador()
    {
        $noticias = $this->service->getService('Noticia')->latestByCategoria('Espaço do Contador');

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

    public function downloadArquivoLfm(SiteRequest $request)
    {
        try{
            $validated = $request->validated();
            $path = public_path() . $validated['arquivo_lfm'];
            if(!\File::exists($path))
                throw new \Exception('Arquivo "' . $validated['arquivo_lfm'] . '" não existe!', 404);

            $indice = strripos($validated['arquivo_lfm'], '/') + 1;
            $nome = substr($validated['arquivo_lfm'], $indice, strlen($validated['arquivo_lfm']));
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            if(in_array($e->getCode(), [404]))
                return redirect()->back()->with(['message' => 'Arquivo não existe!', 'class' => 'alert-danger']);
            abort(500, "Erro ao realizar download do arquivo.");
        }

        return response()->download($path, $nome, ['Cache-Control' => 'no-cache, no-store']);
    }
}
