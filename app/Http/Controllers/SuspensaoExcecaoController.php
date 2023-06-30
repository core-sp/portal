<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
// use App\Http\Requests\SalaReuniaoRequest;

class SuspensaoExcecaoController extends Controller
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
            $dados = $this->service->getService('SalaReuniao')->suspensaoExcecao()->listar(auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os suspensos e liberados.");
        }
    
        return view('admin.crud.home', $dados);
    }

    public function view($id)
    {
        // $this->authorize('viewAny', auth()->user());
        try{
            $dados = $this->service->getService('SalaReuniao')->suspensaoExcecao()->view(auth()->user(), $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao visualizar detalhes do suspenso.");
        }

        return view('admin.crud.mostra', $dados);
    }
}