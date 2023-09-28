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
        $this->authorize('viewAny', auth()->user());
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
        $this->authorize('viewAny', auth()->user());
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
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('SalaReuniao')->suspensaoExcecao()->view(auth()->user(), $id);
            if($situacao == 'excecao'){
                $dados['variaveis']->singular = 'exceção';
                $dados['variaveis']->singulariza = 'a exceção';
            }
            $dados['situacao'] = $situacao;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para editar a ".$situacao.".");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(SuspensaoExcecaoRequest $request, $id, $situacao)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('SalaReuniao')->suspensaoExcecao()->save(auth()->user(), $validated, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar a suspensão.");
        }

        return redirect(route('sala.reuniao.suspensao.lista'))->with(isset($erro['message']) ? $erro : [
            'message' => '<i class="icon fa fa-check"></i>Suspensão com a ID: '.$id.' foi editada com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function create()
    {
        $this->authorize('create', auth()->user());

        try{
            $dados = $this->service->getService('SalaReuniao')->suspensaoExcecao()->view(auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para criar a suspensão.");
        }

        return view('admin.crud.criar', $dados);
    }

    public function store(SuspensaoExcecaoRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('SalaReuniao')->suspensaoExcecao()->save(auth()->user(), $validated);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar a suspensão.");
        }

        return redirect(route('sala.reuniao.suspensao.lista'))->with([
            'message' => '<i class="icon fa fa-check"></i>Suspensão cadastrada com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            if(strlen($request->q) == 0)
                return redirect(route('sala.reuniao.suspensao.lista'))->with([
                    'message' => '<i class="icon fa fa-exclamation-circle"></i> Deve digitar na busca um caracter pelo menos.',
                    'class' => 'alert-warning'
                ]);
            $busca = $request->q;
            $dados = $this->service->getService('SalaReuniao')->suspensaoExcecao()->buscar($busca, auth()->user());
            $dados['busca'] = $busca;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em suspensões.");
        }

        return view('admin.crud.home', $dados);
    }
}