<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AgendamentoBloqueioRequest;
use App\Contracts\MediadorServiceInterface;

class AgendamentoBloqueioController extends Controller
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
            $dados = \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->bloqueio()->listar(auth()->user()) : 
            $this->service->getService('Agendamento')->listarBloqueio();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os bloqueios dos agendamentos.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());

        try{
            $dados = \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->bloqueio()->view(auth()->user(), $this->service) : 
            $this->service->getService('Agendamento')->viewBloqueio(null, $this->service);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para criar o bloqueio do agendamento.");
        }

        return view('admin.crud.criar', $dados);
    }

    public function store(AgendamentoBloqueioRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $validated = $request->validated();
            \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->bloqueio()->save(auth()->user(), $validated) : 
            $this->service->getService('Agendamento')->saveBloqueio($validated, $this->service);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar o bloqueio do agendamento.");
        }

        $rota = \Route::is('sala.reuniao.*') ? 'sala.reuniao.bloqueio.lista' : 'agendamentobloqueios.lista';

        return redirect(route($rota))->with([
            'message' => '<i class="icon fa fa-check"></i>Bloqueio cadastrado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->bloqueio()->view(auth()->user(), null, $id) : 
            $this->service->getService('Agendamento')->viewBloqueio($id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para atualizar o bloqueio do agendamento.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(AgendamentoBloqueioRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->bloqueio()->save(auth()->user(), $validated, $id) : 
            $this->service->getService('Agendamento')->saveBloqueio($validated, $this->service, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o bloqueio do agendamento.");
        }

        $rota = \Route::is('sala.reuniao.*') ? 'sala.reuniao.bloqueio.lista' : 'agendamentobloqueios.lista';

        return redirect(route($rota))->with([
            'message' => '<i class="icon fa fa-check"></i>Bloqueio com a ID: '.$id.' foi editado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());

        try{
            \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->bloqueio()->destroy($id) : 
            $this->service->getService('Agendamento')->delete($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao excluir o bloqueio do agendamento.");
        }

        $rota = \Route::is('sala.reuniao.*') ? 'sala.reuniao.bloqueio.lista' : 'agendamentobloqueios.lista';

        return redirect(route($rota))->with([
            'message' => '<i class="icon fa fa-check"></i>Bloqueio com a ID: '.$id.' foi cancelado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = \Route::is('sala.reuniao.*') ? $this->service->getService('SalaReuniao')->bloqueio()->buscar($busca, auth()->user()) : 
            $this->service->getService('Agendamento')->buscarBloqueio($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em bloqueios.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function getDadosAjax(Request $request)
    {
        $this->authorize('create', auth()->user());

        try{
            if(\Route::is('sala.reuniao.*'))
                $dados = $this->service->getService('SalaReuniao')->getTodasHorasById($request->id);
            else{
                $regional = $this->service->getService('Regional')->getById($request->idregional);
                $dados = [
                    'horarios' => $regional->horariosAge(),
                    'atendentes' => $regional->ageporhorario
                ];
            }
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar os dados da regional.");
        }

        return response()->json($dados);
    }
}
