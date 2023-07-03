<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\SuspensaoExcecaoRequest;

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

    public function edit($id, $situacao)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('SalaReuniao')->suspensaoExcecao()->view(auth()->user(), $id);
            $dados['variaveis']->singular = $situacao;
            $dados['variaveis']->singulariza = $situacao;
            $dados['situacao'] = $situacao;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para editar a ".$situacao.".");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(SuspensaoExcecaoRequest $request, $id, $situacao)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('SalaReuniao')->suspensaoExcecao()->save(auth()->user(), $validated, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar a suspensão.");
        }

        return redirect(route('sala.reuniao.suspensao.lista'))->with([
            'message' => '<i class="icon fa fa-check"></i>Suspensão com a ID: '.$id.' foi editada com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function create()
    {
        // $this->authorize('create', auth()->user());

        try{
            $dados = $this->service->getService('SalaReuniao')->suspensaoExcecao()->view(auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para criar a suspensão.");
        }

        return view('admin.crud.criar', $dados);
    }
}