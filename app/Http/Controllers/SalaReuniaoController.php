<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\SalaReuniaoRequest;

class SalaReuniaoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['getRegionaisAtivas', 'getDiasHoras']]);
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());
        try{
            $dados = $this->service->getService('SalaReuniao')->listar(auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as salas de reuniões.");
        }
        
        $error = session()->get('errors');
        $message = isset($error) && $error->has('file') ? $error->first('file') : null;
        if(isset($message))
        {
            session()->flash('message', '<i class="fas fa-times"></i> <b>Erro de upload do arquivo:</b> ' . $message);
            session()->flash('class', 'alert-danger');
        }
        
        return view('admin.crud.home', $dados);
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        try{
            $dados = $this->service->getService('SalaReuniao')->view($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao visualizar a sala de reunião.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(SalaReuniaoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
        try{
            $validated = $request->validated();
            $this->service->getService('SalaReuniao')->save($validated, $id, auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar a sala de reunião.");
        }

        return redirect()->route('sala.reuniao.index')->with([
            'message' => '<i class="icon fa fa-check"></i>Sala de Reunião com a ID: ' .$id . ' atualizada com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function getRegionaisAtivas($tipo)
    {
        if(!auth()->guard('representante')->check() && !auth()->guard('web')->check())
            return response()->json([], 204);
        try{
            $dados = $this->service->getService('SalaReuniao')->salasAtivas($tipo)->pluck('id');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
        }

        return response()->json($dados);
    }

    public function getDiasHoras(Request $request, $tipo)
    {
        if(!auth()->guard('representante')->check() && !auth()->guard('web')->check())
            return response()->json([], 204);
        try{
            $user = auth()->guard('representante')->check() ? auth()->guard('representante')->user() : null;
            $validate = $request->only('sala_id', 'dia');
            $dados = $this->service->getService('SalaReuniao')->getDiasHoras($tipo, $validate['sala_id'], $validate['dia'], $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
        }

        return response()->json($dados);
    }

    public function getHorarioFormatado(SalaReuniaoRequest $request, $id)
    {
        $this->authorize('viewAny', auth()->user());
        try{
            $validate = $request->validated();
            $horarios = $validate['horarios'];
            $final_manha = $validate['hora_limite_final_manha'];
            $final_tarde = $validate['hora_limite_final_tarde'];
            $dados = $this->service->getService('SalaReuniao')->getHorarioFormatadoById($id, $horarios, $final_manha, $final_tarde);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o horário formatado via ajax.");
        }

        return response()->json($dados);
    }
}