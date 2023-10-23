<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AvisoRequest;
use App\Contracts\MediadorServiceInterface;

class AvisoController extends Controller
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
            $dados = $this->service->getService('Aviso')->listar();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os avisos.");
        }

        return view('admin.crud.home', $dados);
    }

    public function show($id)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Aviso')->show($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o formulário de exemplo do aviso.");
        }

        return view('admin.crud.mostra', $dados);
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('Aviso')->edit($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o formulário de edição do aviso.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(AvisoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $validated = $request->validated();
            $dados = $this->service->getService('Aviso')->save($validated, $id, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o aviso.");
        }

        return redirect()->route('avisos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Aviso com a ID: ' . $id . ' foi editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function updateStatus($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $status = $this->service->getService('Aviso')->updateStatus($id, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status do aviso.");
        }

        return redirect()->route('avisos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Aviso com a ID: ' . $id . ' foi ' .$status. ' com sucesso!')
            ->with('class', 'alert-success');
    }
}
