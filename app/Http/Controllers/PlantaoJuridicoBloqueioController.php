<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PlantaoJuridicoRequest;

class PlantaoJuridicoBloqueioController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->listarBloqueios();
            $variaveis = $dados['variaveis'];
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os bloqueios dos plantões jurídicos.");
        }
    
        return view('admin.crud.home', compact('tabela', 'resultados', 'variaveis'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->visualizarBloqueio();
            $variaveis = $dados['variaveis'];
            $plantoes = $dados['plantoes'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para criar o bloqueio.");
        }

        return view('admin.crud.criar', compact('variaveis', 'plantoes'));
    }

    public function store(PlantaoJuridicoRequest $request)
    {
        $this->authorize('create', auth()->user());
        try{
            $validated = (object) $request->validated();
            $this->service->getService('PlantaoJuridico')->saveBloqueio($validated);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar o bloqueio do plantão jurídico.");
        }

        return redirect(route('plantao.juridico.bloqueios.index'))->with([
            'message' => '<i class="icon fa fa-check"></i>Novo bloqueio criado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function getPlantaoAjax(Request $request)
    {
        $this->authorize('updateOther', auth()->user());
        try{
            $plantao = $this->service->getService('PlantaoJuridico')->getDatasHorasLinkPlantaoAjax($request->id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o plantao.");
        }

        return response()->json($plantao);
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->visualizarBloqueio($id);
            $variaveis = isset($dados['variaveis']) ? $dados['variaveis'] : null;
            $resultado = isset($dados['resultado']) ? $dados['resultado'] : null;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para editar o bloqueio.");
        }

        if(isset($dados['message']))
            return redirect(route('plantao.juridico.bloqueios.index'))->with($dados);

        return view('admin.crud.editar', compact('variaveis', 'resultado'));
    }

    public function update(PlantaoJuridicoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
        try{
            $validated = (object) $request->validated();
            $erro = $this->service->getService('PlantaoJuridico')->saveBloqueio($validated, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o bloqueio do plantão jurídico.");
        }

        return redirect(route('plantao.juridico.bloqueios.index'))->with([
            'message' => isset($erro) ? $erro['message'] : '<i class="icon fa fa-check"></i>Bloqueio com a ID '.$id.' foi atualizado com sucesso!',
            'class' => isset($erro) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());
        try{
            $this->service->getService('PlantaoJuridico')->destroy($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao excluir o bloqueio.");
        }

        return redirect(route('plantao.juridico.bloqueios.index'))->with([
            'message' => '<i class="icon fa fa-check"></i>Bloqueio com a ID '.$id.' foi excluído com sucesso!',
            'class' => 'alert-success'
        ]);
    }
}