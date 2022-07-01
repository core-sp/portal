<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PreRegistroAjaxAdminRequest;

class PreRegistroController extends Controller
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
            $dados = $this->service->getService('PreRegistro')->listar();
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os pré-registros.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function view($id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('PreRegistro')->view($id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
            $abas = $dados['abas'];
            $codigos = $dados['codigos'];
            $classes = $dados['classes'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o pré-registro.");
        }

        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'abas', 'codigos', 'classes'));
    }

    public function updateAjax(PreRegistroAjaxAdminRequest $request, $id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveAjaxAdmin($validatedData, $id, $user);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao salvar a justificativa do pré-registro.");
        }

        return response()->json($dados);
    }

    public function downloadAnexo($idPreRegistro, $id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $file = $this->service->getService('PreRegistro')->downloadAnexo($id, $idPreRegistro);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao fazer download do anexo do pré-registro.");
        }

        return $file;
    }
}