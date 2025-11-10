<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Http\Requests\AvisoRequest;
use App\Contracts\MediadorServiceInterface;

class BdoRepresentanteController extends Controller
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
            $dados = $this->service->getService('Bdo')->admin()->listar(auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os perfis cadastrados dos representantes.");
        }

        return view('admin.crud.home', $dados);
    }

    public function edit($id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('Bdo')->admin()->editar(auth()->user(), $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : abort(500, "Erro ao carregar o formulário de alteração do perfil público do Representante.");
        }

        return view('admin.crud.editar', $dados);
    }
}
