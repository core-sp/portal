<?php

namespace App\Http\Controllers;

use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PerfilRequest;

class PerfilController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $this->service->getService('Perfil')->listar();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os perfis.");
        }

        return view('admin.crud.home', $dados);
    }

    public function create()
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $this->service->getService('Perfil')->view();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar página para criar perfil.");
        }

        return view('admin.crud.criar', $dados);
    }

    public function store(PerfilRequest $request)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $request->validated();
            $dados = $this->service->getService('Perfil')->save($dados);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao salvar o perfil.");
        }
        
        return redirect()->route('perfis.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Perfil cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $this->service->getService('Perfil')->view($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as permissões do perfil.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(PerfilRequest $request, $id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $request->validated();
            $dados = $this->service->getService('Perfil')->save($dados, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao salvar as permissões do perfil.");
        }

        return redirect()->route('perfis.lista')->with($dados);
    }

    public function destroy($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $this->service->getService('Perfil')->delete($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao excluir o perfil.");
        }
        
        return redirect()->back()->with($dados);
    }
}