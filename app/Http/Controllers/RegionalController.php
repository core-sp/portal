<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegionalRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegionalController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid']]);
        $this->service = $service;
    }
    
    public function index()
    {
        try{
            $dados = $this->service->getService('Regional')->index();
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as regionais.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('Regional')->view($id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a regional.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(RegionalRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('Regional')->save($validated, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar a regional.");
        }

        return redirect(route('regionais.index'))->with([
            'message' => '<i class="icon fa fa-check"></i>Informações da regional com id '.$id.' atualizadas com sucesso',
            'class' => 'alert-success'
        ]);
    }

    public function show($id)
    {
        try{
            $dados = $this->service->getService('Regional')->viewSite($id);
            $resultado = $dados['resultado'];
            $noticias = $dados['noticias'];
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Seccional não encontrada.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página da regional no portal.");
        }

        return response()
            ->view('site.regional', compact('resultado','noticias'))
            ->header('Cache-Control','no-cache');
    }

    public function busca(Request $request)
    {
        try{
            $busca = $request->q;
            $dados = $this->service->getService('Regional')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em regionais.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteGrid()
    {
        try{
            $regionais = $this->service->getService('Regional')->all();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as regionais no portal.");
        }
        return response()
            ->view('site.regionais', compact('regionais'))
            ->header('Cache-Control','no-cache');
    }
}