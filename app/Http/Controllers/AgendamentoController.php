<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AgendamentoUpdateRequest;
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
            $dados = $this->service->getService('Agendamento')->listar($request, $this->service);
            $temFiltro = $dados['temFiltro'];
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os agendamentos.");
        }

        return !isset($dados['erro']['message']) ? 
            view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro')) : 
            redirect(session('url'))->with($dados['erro']);
    }

    public function updateStatus(AgendamentoUpdateRequest $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Agendamento')->save($validated);
            $id = $validated['idagendamento'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            method_exists($e, 'getStatusCode') ? abort($e->getStatusCode(), $e->getMessage()) : 
            abort(500, "Erro ao atualizar o status do agendamento.");
        }

        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : 
                '<i class="icon fa fa-check"></i>Status do agendamento com ID '.$id.' foi editado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Agendamento')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao buscar o texto em agendamentos.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('Agendamento')->view($id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
            $atendentes = $dados['atendentes'];
            $servicos = $dados['servicos'];
            $status = $dados['status'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            method_exists($e, 'getStatusCode') ? abort($e->getStatusCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar o agendamento.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'atendentes', 'servicos', 'status'));
    }

    public function update(AgendamentoUpdateRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Agendamento')->save($validated, $id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            method_exists($e, 'getStatusCode') ? abort($e->getStatusCode(), $e->getMessage()) : 
            abort(500, "Erro ao atualizar o agendamento.");
        }

        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : '<i class="icon fa fa-check"></i>Agendamento com a ID '.$id.' foi editado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function reenviarEmail($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $erro = $this->service->getService('Agendamento')->enviarEmail($id);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            method_exists($e, 'getStatusCode') ? abort($e->getStatusCode(), $e->getMessage()) : 
            abort(500, "Erro ao reenviar email do agendamento.");
        }
        
        return redirect(session('url') ?? route('agendamentos.lista'))->with([
            'message' => isset($erro['message']) ? $erro['message'] : '<i class="icon fa fa-check"></i>Email do agendamento com ID '.$id.' foi enviado com sucesso!',
            'class' => isset($erro['class']) ? $erro['class'] : 'alert-success'
        ]);
    }

    public function pendentes()
    {
        $this->authorize('viewPendentes', auth()->user());

        try{
            $dados = $this->service->getService('Agendamento')->listar();
            $resultados = $dados['resultados'];
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os agendamentos pendentes.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }
}