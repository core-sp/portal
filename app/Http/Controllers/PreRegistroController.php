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
        // Limitação de requisições por minuto para cada usuário, senão erro 429
        $this->middleware(['auth', 'throttle:50,1']);
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
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
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
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar o pré-registro.");
        }

        return view('admin.crud.mostra', compact('resultado', 'variaveis', 'abas', 'codigos'));
    }

    public function updateAjax(PreRegistroAjaxAdminRequest $request, $id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveAjaxAdmin($validatedData, $id, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
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
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao fazer download do anexo do pré-registro.");
        }

        return $file;
    }

    public function updateEnviarCorrecao($id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->updateStatus($id, $user, 'corrigir');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao enviar para correção o pré-registro.");
        }

        return redirect(route('preregistro.index'))->with($dados);
    }

    public function updateAprovado($id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->updateStatus($id, $user, 'aprovar');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao aprovar o pré-registro.");
        }

        return redirect(route('preregistro.index'))->with($dados);
    }

    public function updateNegado($id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->updateStatus($id, $user, 'negar');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao negar o pré-registro.");
        }

        return redirect(route('preregistro.index'))->with($dados);
    }
}