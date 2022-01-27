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

    public function create()
    {
        // $this->authorize('create', auth()->user());
        try{
            $dados = $this->service->getService('PlantaoJuridico')->visualizar();
            $variaveis = $dados['variaveis'];
            $regionais = $dados['regionais'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao criar o plantão jurídico.");
        }

        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }
}