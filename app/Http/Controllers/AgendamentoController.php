<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AgendamentoRequest;
use App\Contracts\MediadorServiceInterface;

class AgendamentoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->listar(auth()->user(), $request, $this->service);
            $temFiltro = $dados['temFiltro'];
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os agendamentos.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
    }

    public function updateStatus(AgendamentoRequest $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Agendamento')->save(auth()->user(), $validated);
            $id = $validated['idagendamento'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403,400]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao atualizar o status do agendamento.");
        }

        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : 
                '<i class="icon fa fa-check"></i>Status do agendamento com o código '.$id.' foi editado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Agendamento')->buscar(auth()->user(), $busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em agendamentos.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function edit($id)
    {
        $view = \Route::is('agendamentos.view');
        $view ? $this->authorize('viewAny', auth()->user()) : $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('Agendamento')->view(auth()->user(), $id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
            $atendentes = $dados['atendentes'];
            $servicos = $dados['servicos'];
            $status = $dados['status'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar o agendamento.");
        }

        return $view ? view('admin.crud.mostra', compact('resultado', 'variaveis')) : view('admin.crud.editar', compact('resultado', 'variaveis', 'atendentes', 'servicos', 'status'));
    }

    public function update(AgendamentoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Agendamento')->save(auth()->user(), $validated, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403,400]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao atualizar o agendamento.");
        }

        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : '<i class="icon fa fa-check"></i>Agendamento com o código '.$id.' foi editado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function reenviarEmail($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $erro = $this->service->getService('Agendamento')->enviarEmail(auth()->user(), $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao reenviar email do agendamento.");
        }
        
        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : '<i class="icon fa fa-check"></i>Email do agendamento com o código '.$id.' foi enviado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function pendentes()
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->listar(auth()->user());
            $resultados = $dados['resultados'];
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os agendamentos pendentes.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }
}