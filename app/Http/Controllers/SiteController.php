<?php

namespace App\Http\Controllers;

use Exception;
use App\Pagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Repositories\CompromissoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;
use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiApiRepository;
use Carbon\Carbon;

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
        $imagens = $this->service->getService('HomeImagem')->carrossel()['resultado']->whereNotNull('url');
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

    public function testeApis(GerentiApiRepository $apiGerenti, Request $request)
    {
        $dados = [];
        $message = '';

        if(\Route::is('api-simulador')){
            $tipoAssociado = (int) $request->tipoAssociado;
            $dataInicio = Carbon::create($request->dataInicio)->toISOString();
            $capitalSocial = $tipoAssociado == 1 ? (float) str_replace(',', '.', str_replace('.', '', $request->capitalSocial)) : 0;
            $message = 'API - Simulador';
            $dados = $apiGerenti->gerentiSimulador($tipoAssociado, $dataInicio, $capitalSocial);
        }

        if(\Route::is('api-tipos-contatos')){
            $message = 'API - Tipos Contatos';
            $dados = $apiGerenti->gerentiTiposContatos();
        }

        if(\Route::is('api-contatos')){
            $message = 'API - Contatos Representante';
            $dados = $apiGerenti->gerentiGetContatos((int) $request->ass_id, $request->tipo);
        }

        if(\Route::is('api-enderecos')){
            $message = 'API - Endereços Representante';
            $dados = $apiGerenti->gerentiGetEnderecos((int) $request->ass_id);
        }

        if(\Route::is('api-extrato')){
            $message = 'API - Extrato Representante';
            $dados = ['data' => $apiGerenti->gerentiGetExtrato((int) $request->ass_id)];
        }

        if(\Route::is('api-segmentos')){
            $message = 'API - Segmentos';
            $dados = $apiGerenti->gerentiSegmentos();
        }

        if(\Route::is('api-dados-representante')){
            $message = 'API - Dados do Representante';
            $dados = $apiGerenti->gerentiDadosRepresentante((int) $request->ass_id);
        }

        if(\Route::is('api-representante-registrado')){
            $message = 'API - Representante Registrado';
            $dados = $apiGerenti->gerentiRepresentanteRegistrado($request->registro, $request->cpf_cnpj, $request->email);
        }

        if(\Route::is('api-validar-representante')){
            $message = 'API - Validar Representante';
            $dados = $apiGerenti->gerentiValidarRepresentante((int) $request->ass_id);
        }

        return view('site.teste-apis-gerenti', compact('dados', 'message'));
    }
}
