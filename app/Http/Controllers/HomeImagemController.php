<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;

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
            $dados = $this->service->getService('Geral')->carrossel();
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
            $dados = $this->service->getService('Geral')->carrossel($array);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar os dados das imagens do carrossel.");
        }

        return redirect('/admin')
            ->with('message', '<i class="icon fa fa-check"></i>Banner editado com sucesso!')
            ->with('class', 'alert-success');
    }
}
