<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LicitacaoRequest;
use App\Contracts\MediadorServiceInterface;

class LicitacaoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid', 'siteBusca']]);
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Licitacao')->listar();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar as licitações.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());

        try{
            $dados = $this->service->getService('Licitacao')->view();
            $variaveis = $dados['variaveis'];
            $modalidades = $dados['modalidades'];
            $situacoes = $dados['situacoes'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar a página para criar a licitação.");
        }

        return view('admin.crud.criar', compact('variaveis', 'modalidades', 'situacoes'));
    }

    public function store(LicitacaoRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $this->service->getService('Licitacao')->save($validated, $user);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao criar a licitação.");
        }

        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('Licitacao')->view($id);
            $variaveis = $dados['variaveis'];
            $resultado = $dados['resultado'];
            $modalidades = $dados['modalidades'];
            $situacoes = $dados['situacoes'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar a página para editar a licitação.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'modalidades', 'situacoes'));
    }

    public function update(LicitacaoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $this->service->getService('Licitacao')->save($validated, $user, $id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao editar a licitação.");
        }

        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação com a ID: ' . $id . ' foi editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($id)
    {
        try{
            $licitacao = $this->service->getService('Licitacao')->viewSite($id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar a página da licitação no portal.");
        }

        return response()
            ->view('site.licitacao', compact('licitacao'))
            ->header('Cache-Control','no-cache');
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());

        try{
            $this->service->getService('Licitacao')->destroy($id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao excluir a licitação.");
        }

        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação com a ID: ' . $id . ' foi deletada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function lixeira()
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $this->service->getService('Licitacao')->lixeira();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar as licitações excluídas.");
        }

        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->authorize('onlyAdmin', auth()->user());
        
        try{
            $this->service->getService('Licitacao')->restore($id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao restaurar a licitação.");
        }

        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação com a ID: ' . $id . ' foi restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Licitacao')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em licitações.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteGrid()
    {
        try{
            $dados = $this->service->getService('Licitacao')->siteGrid();
            $licitacoes = $dados['licitacoes'];
            $modalidades = $dados['modalidades'];
            $situacoes = $dados['situacoes'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as licitações no portal.");
        }

        return response()
            ->view('site.licitacoes', compact('licitacoes', 'modalidades', 'situacoes'))
            ->header('Cache-Control','no-cache');
    }

    public function siteBusca(LicitacaoRequest $request)
    {
        try{
            $validated = $request->validated();
            $dados = $this->service->getService('Licitacao')->siteGrid($validated);
            $licitacoes = $dados['licitacoes'];
            $modalidades = $dados['modalidades'];
            $situacoes = $dados['situacoes'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar as licitações no portal.");
        }

        return view('site.licitacoes', compact('licitacoes', 'modalidades', 'situacoes'));
    }
}