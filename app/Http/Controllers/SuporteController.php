<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\SuporteRequest;
use Illuminate\Support\Facades\View;

class SuporteController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;

        if(isset($service) && \Route::is('suporte.log.externo.*'))
        {
            $dados = $this->service->getService('Suporte')->indexLog();
            $info = $dados['info'];
            $variaveis = $dados['variaveis'];
            View::share([
                'info' => $info, 
                'variaveis' => $variaveis
            ]);
        }
    }

    public function logExternoIndex()
    {
        $this->authorize('onlyAdmin', auth()->user());
    
        return view('admin.crud.mostra');
    }

    public function viewLogExternoDoDia()
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $log = $this->service->getService('Suporte')->logPorData(date('Y-m-d'));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o log do dia de hoje.");
        }

        return isset($log) ? $log : redirect()->back()->with([
            'message' => '<i class="icon fa fa-ban"></i>Ainda não há log do dia de hoje: '.date('d/m/Y'),
            'class' => 'alert-warning'
        ]);
    }

    public function buscaLogExterno(SuporteRequest $request)
    {
        $request->validated();

        $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('Suporte')->logBusca($request);
            $busca = isset($request->data) ? onlyDate($request->data) : $request->texto;
            $resultado = $dados['resultado'];
            $tipo = isset($request->data) ? 'data' : 'texto';
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o log da busca.");
        }

        return view('admin.crud.mostra', compact('resultado', 'tipo', 'busca'));
    }

    public function viewLogExterno($data)
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $log = $this->service->getService('Suporte')->logPorData($data);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o log da data escolhida.");
        }

        return isset($log) ? $log : redirect()->back()->with([
            'message' => '<i class="icon fa fa-ban"></i>Não há log do dia: '.onlyDate($data),
            'class' => 'alert-warning'
        ]);
    }

    public function errosIndex()
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('Suporte')->indexErros();
            $erros = $dados['erros'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar a tabela de erros.");
        }
    
        return view('admin.crud.mostra', compact('erros', 'variaveis'));
    }

    public function uploadFileErros(SuporteRequest $request)
    {
        $request->validated();
        $liberado = auth()->user()->can('onlyAdmin', auth()->user()) && auth()->user()->email == 'desenvolvimento@core-sp.org.br';
        abort_if(!$liberado, 403);
        try{
            $this->service->getService('Suporte')->uploadFileErros($request->file);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar a tabela de erros.");
        }
    
        return redirect()->back()->with([
            'message' => '<i class="icon fa fa-check"></i>Arquivo atualizado com sucesso!',
            'class' => 'alert-success'
        ]);
    }

    public function getErrosFile()
    {
        $liberado = auth()->user()->can('onlyAdmin', auth()->user()) && auth()->user()->email == 'desenvolvimento@core-sp.org.br';
        abort_if(!$liberado, 403);
        try{
            $path = $this->service->getService('Suporte')->getFileErros();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar a tabela de erros.");
        }
    
        return isset($path) ? response()->download($path) : redirect()->back()->with([
            'message' => '<i class="icon fa fa-ban"></i>Não há arquivo',
            'class' => 'alert-warning'
        ]);
    }
}