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
        $this->middleware(['auth', 'throttle:100,1']);
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $filtro = \Route::is('preregistro.filtro') ? true : null;
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->admin()->listar($request, $this->service, $user, $filtro);
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
        $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('PreRegistro')->admin()->view($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar o pré-registro.");
        }

        return view('admin.crud.mostra', $dados);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('PreRegistro')->admin()->buscar($request->q, auth()->user());
            $dados['busca'] = $request->q;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em pré-registros.");
        }

        return view('admin.crud.home', $dados);
    }

    public function updateAjax(PreRegistroAjaxAdminRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $user = auth()->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->admin()->saveAjaxAdmin($validatedData, $id, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao salvar a justificativa do pré-registro.");
        }

        return response()->json($dados);
    }

    public function downloadAnexo($idPreRegistro, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $file = $this->service->getService('PreRegistro')->downloadAnexo($id, $idPreRegistro, true);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao fazer download do anexo do pré-registro.");
        }

        return response()->file($file, ["Cache-Control" => "no-cache"]);
    }

    public function updateStatus(PreRegistroAdminRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $dados = $this->service->getService('PreRegistro')->admin()->updateStatus($id, $user, $validated['status']);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status do pré-registro.");
        }

        $link = session()->has('url_pre_registro') ? session('url_pre_registro') : route('preregistro.index');
        
        return redirect($link)->with($dados);
    }

    public function uploadDoc(PreRegistroAdminRequest $request, $preRegistro)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $request->validated();
            $dados = $this->service->getService('PreRegistro')->admin()->uploadDoc($preRegistro, $dados['file'], $dados['tipo']);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao fazer upload do documento no pré-registro.");
        }

        return redirect()->route('preregistro.view', $preRegistro)->with($dados);
    }
}