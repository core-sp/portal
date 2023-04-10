<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GeralRequest;
use App\Contracts\MediadorServiceInterface;

class SiteController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
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
}
