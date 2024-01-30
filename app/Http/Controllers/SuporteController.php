<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\SuporteRequest;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

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
            View::share($dados);
        }
    }

    public function logExternoIndex()
    {
        $this->authorize('onlyAdmin', auth()->user());
    
        return view('admin.crud.mostra');
    }

    public function viewLogExternoDoDia($tipo)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $log = $this->service->getService('Suporte')->logPorData(date('Y-m-d'), $tipo);
            $headers = [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store',
                'Content-Disposition' => 'inline; filename="laravel-'.date('Y-m-d').'.log"'
            ];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o log " . $tipo . " do dia de hoje.");
        }

        return isset($log) ? response($log)->withHeaders($headers) : redirect()->back()->with([
            'message' => '<i class="icon fa fa-ban"></i>Ainda não há log ' . $tipo . ' do dia de hoje: '.date('d/m/Y'),
            'class' => 'alert-warning'
        ]);
    }

    public function buscaLogExterno(SuporteRequest $request)
    {
        $semCache = !Cache::has('request_busca_log_'.auth()->id()) || (Cache::get('request_busca_log_'.auth()->id()) !== $request->except(['page', '_token']));
        
        try{
            $validated = $request->validated();

            $dados = $semCache ? $this->service->getService('Suporte')->logBusca($validated) : 
            ['resultado' => Cache::get('resultado_busca_log_'.auth()->id()), 'totalFinal' => Cache::get('totalFinal_busca_log_'.auth()->id())];

            $busca = isset($request['data']) ? onlyDate($request['data']) : $request['texto'];
            $resultado = is_array($dados['resultado']) ? $this->paginate($dados['resultado']) : $dados['resultado'];
            $totalFinal = $dados['totalFinal'];

            if($semCache)
            {
                Cache::put('resultado_busca_log_'.auth()->id(), $dados['resultado'], now()->addMinutes(15));
                Cache::put('request_busca_log_'.auth()->id(), $request->except(['page', '_token']), now()->addMinutes(15));
                Cache::put('totalFinal_busca_log_'.auth()->id(), $totalFinal, now()->addMinutes(15));
            }
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o resultado da busca de log(s).");
        }
        
        return view('admin.crud.mostra', compact('resultado', 'busca', 'totalFinal'));
    }

    public function viewLogExterno($data, $tipo)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $log = $this->service->getService('Suporte')->logPorData($data, $tipo);
            $headers = [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store',
                'Content-Disposition' => 'inline; filename="laravel-'.$data.'.log"'
            ];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o log " . $tipo . " do dia " .onlyDate($data). ".");
        }

        return isset($log) ? response($log)->withHeaders($headers) : redirect()->back()->with([
            'message' => '<i class="icon fa fa-ban"></i>Não há log ' . $tipo . ' do dia: '.onlyDate($data),
            'class' => 'alert-warning'
        ]);
    }

    public function downloadLogExterno($data, $tipo)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $log = $this->service->getService('Suporte')->logPorData($data, $tipo);
            $nome = 'laravel-'.$data.'.txt';
            $headers = [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store',
            ];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao realizar o download do log " . $tipo . " do dia " .onlyDate($data). ".");
        }

        return isset($log) ? response()->streamDownload(function() use($log){
                echo $log;
            }, $nome, $headers) : redirect()->back()->with([
            'message' => '<i class="icon fa fa-ban"></i>Não há log ' . $tipo . ' do dia: '.onlyDate($data),
            'class' => 'alert-warning'
        ]);
    }

    public function relatorios(SuporteRequest $request)
    {
        try{
            $dados = $request->validated();
            $relat = $this->service->getService('Suporte')->relatorios($dados);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            if(in_array($e->getCode(), [400]))
                return redirect()->route('suporte.log.externo.index')->with(['message' => $e->getMessage(), 'class' => 'alert-danger']);
            abort(500, "Erro ao gerar relatório.");
        }

        return redirect()->route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']);
    }

    public function relatoriosAcoes($relat, $acao)
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $dados = $this->service->getService('Suporte')->relatorios($relat, $acao);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            if(in_array($e->getCode(), [404]))
                return redirect()->route('suporte.log.externo.index')->with(['message' => $e->getMessage(), 'class' => 'alert-danger']);
            abort(500, "Erro ao realizar ação com o relatório.");
        }

        if($acao == 'exportar-csv')
            return response()->stream($dados['final'], 200, $dados['headers']);
        return isset($dados) ? view('admin.views.log_relatorio', ['tabela' => $dados['tabela'], 'relat' => $relat]) : redirect()->route('suporte.log.externo.index')->with('relat_removido', $relat);
    }

    public function relatorioFinal()
    {
        $this->authorize('onlyAdmin', auth()->user());

        try{
            $relat = $this->service->getService('Suporte')->relatorioFinal();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao gerar relatório final.");
        }

        return redirect()->route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'visualizar']);
    }

    public function errosIndex()
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('Suporte')->indexErros();
            $erros = $dados['erros'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
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
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
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
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a tabela de erros.");
        }
    
        return isset($path) ? response()->download($path) : redirect()->back()->with([
            'message' => '<i class="icon fa fa-ban"></i>Não há arquivo',
            'class' => 'alert-warning'
        ]);
    }

    public function ipsView()
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $dados = $this->service->getService('Suporte')->ips();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os ips bloqueados.");
        }
    
        return view('admin.crud.mostra', $dados);
    }

    public function ipsExcluir($ip)
    {
        $this->authorize('onlyAdmin', auth()->user());
        try{
            $user = auth()->user();
            $this->service->getService('Suporte')->liberarIp($ip, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao liberar ip.");
        }
    
        return redirect()->route('suporte.ips.view')->with([
            'message' => '<i class="icon fa fa-check"></i>Tabela de IPs atualizada!',
            'class' => 'alert-success'
        ]);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    private function paginate($items, $perPage = 10)
    {
        $pageStart = request('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;
        $itemsForCurrentPage = array_slice($items, $offSet, $perPage, TRUE);

        return new LengthAwarePaginator(
            $itemsForCurrentPage, count($items), $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );
    }
}