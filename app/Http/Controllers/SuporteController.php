<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;

class SuporteController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function logExternoIndex()
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('Suporte')->indexLog();
            $info = $dados['info'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o serviço de suporte.");
        }

        return view('admin.crud.mostra', compact('info', 'variaveis'))->with([
            'message' => 'Ainda não há log do dia de hoje: '.date('d/m/Y'),
            'class' => 'alert-warning'
        ]);
    }

    public function viewLogExternoDoDia()
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $log = $this->service->getService('Suporte')->logDoDia();
            $headers = [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'inline; filename="laravel-'.date('Y').'-'.date('m').'-'.date('d').'.log"'
            ];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o log do dia de hoje.");
        }

        return isset($log) ? response()->stream($log, 200, $headers) : redirect()->back()->with([
            'message' => 'Ainda não há log do dia de hoje: '.date('d/m/Y'),
            'class' => 'alert-warning'
        ]);
    }

    public function buscaLogExterno(Request $request)
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $resultado = $this->service->getService('Suporte')->busca($request->all());
            $tipo = isset($request->data) ? 'data' : 'texto'; 
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o log da busca.");
        }

        return redirect()->back()->with(compact('resultado', 'tipo'));
    }

    public function viewLogExterno($data)
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $log = $this->service->getService('Suporte')->logPorData($data);
            $headers = [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'inline; filename="laravel-'.$data.'.log"'
            ];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar o log da data escolhida.");
        }

        return isset($log) ? response()->stream($log, 200, $headers) : redirect()->back()->with([
            'message' => 'Ainda não há log do dia: '.$data,
            'class' => 'alert-warning'
        ]);
    }
}