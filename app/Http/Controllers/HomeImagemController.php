<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\HomeImagemRequest;

class HomeImagemController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }
    
    public function editBanner()
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('HomeImagem')->carrossel();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados das imagens do carrossel.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function updateBanner(Request $request)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $array = $request->except(['_token', '_method']);
            $dados = $this->service->getService('HomeImagem')->carrossel($array);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return $e->getCode() == 400 ? redirect()->route('imagens.banner')->with(['message' => $e->getMessage(), 'class' => 'alert-danger']) : abort(500, "Erro ao atualizar os dados das imagens do carrossel.");
        }

        return redirect('/admin')
            ->with('message', '<i class="icon fa fa-check"></i>Banner editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function editItensHome()
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('HomeImagem')->itensHome();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados dos itens da home.");
        }

        return view('admin.crud.editar', $dados);
    }

    public function updateItensHome(HomeImagemRequest $request)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $dados = $this->service->getService('HomeImagem')->itensHome($validated);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar os dados dos itens da home.");
        }

        return redirect()->route('imagens.itens.home')
            ->with('message', '<i class="icon fa fa-check"></i>Itens da home editados com sucesso!')
            ->with('class', 'alert-success');
    }

    public function storageItensHome(HomeImagemRequest $request)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $dados = \Route::is('imagens.itens.home.storage.post') ? 
            $this->service->getService('HomeImagem')->uploadFileStorage($validated['file_itens_home']) : $this->service->getService('HomeImagem')->itensHomeStorage();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os arquivos do storage dos itens da home.");
        }

        return response()->json($dados);
    }

    public function destroyFile($file)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('HomeImagem')->itensHomeStorage($file);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao excluir o arquivo do storage dos itens da home.");
        }

        return response()->json($dados);
    }
}