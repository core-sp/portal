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
            $dados = $this->service->getService('PreRegistro')->getAdminService()->listar($request, $this->service, $user);
            session(['url_pre_registro' => url()->full()]);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar os pré-registros.");
        }

        return view('admin.crud.home', $dados);
    }

    public function view($id)
    {
        // $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('PreRegistro')->getAdminService()->view($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar o pré-registro.");
        }

        return view('admin.crud.mostra', $dados);
    }

    public function busca(Request $request)
    {
        // $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('PreRegistro')->getAdminService()->buscar($request->q);
            $dados['busca'] = $request->q;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em pré-registros.");
        }

        return view('admin.crud.home', $dados);
    }

    public function updateAjax(PreRegistroAjaxAdminRequest $request, $id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->getAdminService()->saveAjaxAdmin($validatedData, $id, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
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
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao fazer download do anexo do pré-registro.");
        }

        return response()->file($file, ["Cache-Control" => "no-cache"]);
    }

    public function updateStatus(PreRegistroAdminRequest $request, $id)
    {
        // $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->getAdminService()->updateStatus($id, $user, $validated['status']);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status do pré-registro.");
        }

        return redirect(session('url_pre_registro') ?? route('preregistro.index'))->with($dados);
    }
}