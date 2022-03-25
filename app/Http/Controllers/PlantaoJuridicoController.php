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
        $this->authorize('viewAny', auth()->user());
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
        $this->authorize('updateOther', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->visualizar($id);
            $variaveis = $dados['variaveis'];
            $resultado = $dados['resultado'];
            $agendamentos = $dados['agendamentos'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao visualizar o plantão jurídico.");
        }

        return view('admin.crud.editar', compact('variaveis', 'resultado', 'agendamentos'));
    }

    public function update(PlantaoJuridicoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
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
}