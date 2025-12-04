<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BdoPerfilRequest;
use App\Repositories\GerentiRepositoryInterface;
use App\Contracts\MediadorServiceInterface;

class BdoRepresentanteController extends Controller
{
    private $service;
    private $gerentiRepository;

    public function __construct(MediadorServiceInterface $service, GerentiRepositoryInterface $gerentiRepository)
    {
        $this->middleware('auth');
        $this->service = $service;
        $this->gerentiRepository = $gerentiRepository;
    }  

    public function index()
    {
        // $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Bdo')->admin()->listar(auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os perfis cadastrados dos representantes.");
        }

        return view('admin.crud.home', $dados);
    }

    public function edit($id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('Bdo')->admin()->editar(auth()->user(), $id);
            $this->service->getService('Representante')->dadosBdoGerenti($dados['resultado']->representante, $this->gerentiRepository, $dados['gerenti']);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : abort(500, "Erro ao carregar o formulário de alteração do perfil público do Representante.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function update(BdoPerfilRequest $request, $id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $dados = $this->service->getService('Bdo')->admin()->save($validated, $id, auth()->user());
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : abort(500, "Erro ao atualizar o status da solicitação do perfil público.");
        }

        return redirect()->route('bdorepresentantes.lista')->with($dados);
    }
}
