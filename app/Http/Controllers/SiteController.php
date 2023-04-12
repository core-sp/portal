<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GeralRequest;
use App\Contracts\MediadorServiceInterface;
use App\Repositories\GerentiRepositoryInterface;

class SiteController extends Controller
{
    private $service;
    private $gerentiRepository;

    public function __construct(MediadorServiceInterface $service, GerentiRepositoryInterface $gerentiRepository)
    {
        $this->service = $service;
        $this->gerentiRepository = $gerentiRepository;
    }

    public function index()
    {
        try{
            $latestNoticias = $this->service->getService('Noticia')->latest();
            $noticias = $latestNoticias['noticias'];
            $cotidianos = $latestNoticias['cotidianos'];
            $imagens = $this->service->getService('Geral')->carrossel()['resultado'];
            $posts = $this->service->getService('Post')->latest();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados no portal.");
        }

        return response()
            ->view('site.home', compact('noticias','cotidianos','imagens', 'posts'))
            ->header('Cache-Control','no-cache');
    }

    public function busca(GeralRequest $request)
    {
        try{
            $busca = $request->validated()['busca'];
            $buscaArray = preg_split('/\s+/', $busca, -1);

            if(isset($busca))
            {
                $paginas = $this->service->getService('Pagina')->buscaSite($buscaArray)->get();
                $noticias = $this->service->getService('Noticia')->buscaSite($buscaArray)->get();
                $posts = $this->service->getService('Post')->buscaSite($buscaArray)->get();
                $resultados = collect([$paginas, $noticias, $posts])->collapse();
            }
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar texto no portal.");
        }

        return isset($busca) ? view('site.busca', compact('busca', 'resultados')) : redirect()->route('site.home');
    }

    public function feiras()
    {
        try{
            $noticias = $this->service->getService('Noticia')->latestByCategoria('Feiras');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as feiras no portal.");
        }

        return view('site.feiras', compact('noticias'));
    }

    public function views_geral()
    {
        if(\Route::is('consultaSituacao'))
            return view('site.consulta');

        if(\Route::is('anuidade-ano-vigente'))
            return view('site.anuidade-ano-vigente');
    }

    public function consultaSituacao(GeralRequest $request)
    {
        try{
            $cpfCnpj = apenasNumeros($request->validated()['cpfCnpj']);
            $dados_gerenti = $this->gerentiRepository->gerentiAtivo($cpfCnpj);
            $resultado = $this->service->getService('Geral')->consultaSituacao($dados_gerenti);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao consultar a situação no portal.");
        }

        return view('site.consulta', compact('resultado'));
    }

    public function anuidadeVigente(GeralRequest $request)
    {
        try{
            $cpfCnpj = apenasNumeros($request->validated()['cpfCnpj']);
            $dados_gerenti = $this->gerentiRepository->gerentiAnuidadeVigente($cpfCnpj);
            $resultado = $this->service->getService('Geral')->anuidadeVigente($dados_gerenti);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao consultar a anuidade vigente no portal.");
        }

        return view('site.anuidade-ano-vigente', compact('resultado'));
    }
}
