<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PreRegistroAjaxAdminRequest;
use App\Http\Requests\PreRegistroAdminRequest;

class PreRegistroController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        // Limitação de requisições por minuto para cada usuário, senão erro 429
        $qtd = '50';
        if(config('app.env') == "testing")
            $qtd = '190';
        $this->middleware(['auth', 'throttle:' . $qtd . ',1']);
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // $this->authorize('viewAny', auth()->user());

        try{
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->listar($request, $this->service, $user);
            $temFiltro = $dados['temFiltro'];
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar os pré-registros.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
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

    public function busca(Request $request)
    {
        // $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('PreRegistro')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em pré-registros.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
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

    public function updateStatus(PreRegistroAdminRequest $request, $id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->updateStatus($id, $user, $validated['status']);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status do pré-registro.");
        }

        return redirect(session('url_pre_registro') ?? route('preregistro.index'))->with($dados);
    }
}