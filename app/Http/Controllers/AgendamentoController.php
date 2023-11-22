<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AgendamentoRequest;
use App\Http\Requests\AgendamentoSalaVerificaRequest;
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
            $filtro = \Route::is('sala.reuniao.agendados.filtro') ? true : null;
            $dados = \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->agendados()->listar(auth()->user(), $filtro, $request, $this->service) : 
            $this->service->getService('Agendamento')->listar($request, $this->service);
            $temFiltro = $dados['temFiltro'];
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os agendamentos.");
        }

        if(\Route::is('sala.reuniao.*'))
            $this->service->getService('SalaReuniao')->site()->limparVerificadosConselho(request()->session());

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
    }

    public function updateStatus(AgendamentoRequest $request, $id = null, $acao = null)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $validated = $request->validated();
            $erro = \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->agendados()->update(auth()->user(), $id, $acao, $validated) : 
            $this->service->getService('Agendamento')->save($validated);
            $id = \Route::is('sala.reuniao.*') ? $id : $validated['idagendamento'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403,400]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao atualizar o status do agendamento.");
        }

        $rota = \Route::is('sala.reuniao.*') ? 'sala.reuniao.agendados.index' : 'agendamentos.lista';

        return redirect(session('url') ?? route($rota))->with([
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
            $dados = \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->agendados()->buscar(auth()->user(), $busca) : 
            $this->service->getService('Agendamento')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em agendamentos.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function create()
    {
        $user = auth()->user();
        $this->authorize('create', $user);
        
        try{
            $dados = $this->service->getService('SalaReuniao')->agendados()->view();
            $dados['salas'] = $this->service->getService('SalaReuniao')->salasAtivas();
            $dados['salas'] = in_array($user->idperfil, [8, 21]) && $dados['salas']->isNotEmpty() ? $dados['salas']->where('idregional', $user->idregional) : $dados['salas'];

            if($dados['salas']->isEmpty())
                return redirect()->route('sala.reuniao.agendados.index')
                ->with(['message' => 'Não possui salas ativas para criar agendamento!', 'class' => 'alert-danger']);

        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403, 404]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar a página para criar o agendamento de sala.");
        }

        return view('admin.crud.criar', $dados);
    }

    public function verificar(AgendamentoSalaVerificaRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $dados = $request->validated();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao verificar dados para criar agendamento de sala.");
        }

        return response()->json($dados);
    }

    public function store(AgendamentoSalaVerificaRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $validated = $request->validated();
            $dados = $this->service->getService('SalaReuniao')->agendados()->save($validated, auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar agendamento de sala.");
        }

        $this->service->getService('SalaReuniao')->site()->limparVerificadosConselho(request()->session());

        return redirect()->route('sala.reuniao.agendados.busca', ['q' => $dados['protocolo']])->with($dados);
    }

    public function view($id, $anexo = null)
    {
        $this->authorize('viewAny', auth()->user());
        
        try{
            $dados = $this->service->getService('SalaReuniao')->agendados()->view(auth()->user(), $id, $anexo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403, 404]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar o agendamento.");
        }

        return isset($anexo) ? response()->file($dados, ["Cache-Control" => "no-cache"]) : view('admin.crud.mostra', $dados);
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
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar o agendamento.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'atendentes', 'servicos', 'status'));
    }

    public function update(AgendamentoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Agendamento')->save($validated, $id);
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
            $erro = $this->service->getService('Agendamento')->enviarEmail($id);
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
            $dados = $this->service->getService('Agendamento')->listar();
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