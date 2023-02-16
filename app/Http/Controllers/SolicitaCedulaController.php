<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SolicitaCedulaRequest;
use App\Contracts\MediadorServiceInterface;

class SolicitaCedulaController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function show($id)
    {
        $this->authorize('updateShow', auth()->user());

        try{
            $dados = $this->service->getService('Cedula')->view($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para visualizar a solicitação de cédula.");
        }

        return view('admin.crud.mostra')->with($dados);
    }

    public function updateStatus(SolicitaCedulaRequest $request, $id)
    {
        $this->authorize('updateShow', auth()->user());
        try {
            $validated = $request->validated();
            $dados = $this->service->getService('Cedula')->updateStatus($id, $validated, auth()->user());
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao atualizar o status da solicitação de cédula.");
        }

        $txt = isset($validated['justificativa']) ? 'recusada.' : 'aceita.';

        return redirect()->route('solicita-cedula.index')->with([
            'message' => '<i class="fas fa-check"></i> A solicitação de cédula com a ID: ' . $id . ' foi ' . $txt,
            'class' => 'alert-success'
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Cedula')->listar($request);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as solicitações de cédulas.");
        }

        return view('admin.crud.home')->with($dados);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Cedula')->buscar($request->q);
            $dados['busca'] = $request->q;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em solicitações de cédulas.");
        }

        return view('admin.crud.home')->with($dados);
    }

    public function gerarPdf($id)
    {
        $this->authorize('updateShow', auth()->user());

        try{
            $dados = $this->service->getService('Cedula')->gerarPdf($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao gerar o pdf da solicitação de cédula.");
        }

        if(isset($dados['stream']))
            return $dados['stream'];

        return redirect()->route('solicita-cedula.index')->with([
            'message' => '<i class="fas fa-ban"></i> A solicitação de cédula não foi aceita.',
            'class' => 'alert-danger'
        ]);
    }
}
