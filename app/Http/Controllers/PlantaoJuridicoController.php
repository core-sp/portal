<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PlantaoJuridicoRequest;

class PlantaoJuridicoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        // $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->listar();
            $variaveis = $dados['variaveis'];
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os plantões jurídicos.");
        }
    
        return view('admin.crud.home', compact('tabela', 'resultados', 'variaveis'));
    }

    public function edit($id)
    {
        // $this->authorize('create', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->visualizar($id);
            $variaveis = $dados['variaveis'];
            $resultado = $dados['resultado'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao visualizar o plantão jurídico.");
        }

        return view('admin.crud.editar', compact('variaveis', 'resultado'));
    }

    public function update(PlantaoJuridicoRequest $request, $id)
    {
        // $this->authorize('create', auth()->user());
        try{
            $validated = (object) $request->validated();
            $this->service->getService('PlantaoJuridico')->save($validated, $id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao atualizar o plantão jurídico.");
        }

        return redirect(route('plantao.juridico.index'))->with([
            'message' => '<i class="icon fa fa-check"></i>Plantão Jurídico atualizado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function indexBloqueios()
    {
        // $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->listarBloqueios();
            $variaveis = $dados['variaveis'];
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os bloqueios dos plantões jurídicos.");
        }
    
        return view('admin.crud.home', compact('tabela', 'resultados', 'variaveis'));
    }

    public function create()
    {
        // $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->visualizarBloqueio();
            $variaveis = $dados['variaveis'];
            $plantoes = $dados['plantoes'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar a página para criar o bloqueio.");
        }

        return view('admin.crud.criar', compact('variaveis', 'plantoes'));
    }

    public function store(PlantaoJuridicoRequest $request)
    {
        // $this->authorize('create', auth()->user());
        try{
            $validated = (object) $request->validated();
            $erro = $this->service->getService('PlantaoJuridico')->saveBloqueio($validated);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao criar o bloqueio do plantão jurídico.");
        }

        return redirect(route('plantao.juridico.bloqueios.criar'))->with([
            'message' => isset($erro) ? $erro['message'] : '<i class="icon fa fa-check"></i>Novo bloqueio criado com sucesso!',
            'class' => isset($erro) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function getPlantaoAjax(Request $request)
    {
        // $this->authorize('onlyAdmin', auth()->user());
        try{
            $plantao = $this->service->getService('PlantaoJuridico')->getDatasHorasPlantaoAjax($request->id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao buscar o plantao.");
        }

        return response()->json($plantao);
    }
}