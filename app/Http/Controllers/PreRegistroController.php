<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PreRegistroRequest;

class PreRegistroController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        // $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('PreRegistro')->listar();
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os pré-registros.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function view($id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('PreRegistro')->view($id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
            $abas = $dados['abas'];
            $codigos = $dados['codigos'];
            $classes = $dados['classes'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o pré-registro.");
        }

        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'abas', 'codigos', 'classes'));
    }

    public function updateAjax(Request $request)
    {
        // $this->authorize('updateOther', auth()->user());

        // try{
        //     $dados = $this->service->getService('PreRegistro')->view($id);
        //     $resultado = $dados['resultado'];
        //     $variaveis = $dados['variaveis'];
        // } catch (\Exception $e) {
        //     \Log::error($e->getMessage());
        //     abort(500, "Erro ao carregar o pré-registro.");
        // }

        return response()->json();
    }
}