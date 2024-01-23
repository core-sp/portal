<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PeriodoFiscalizacaoRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FiscalizacaoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['mostrarMapa', 'mostrarMapaPeriodo']]);
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Fiscalizacao')->listar();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os períodos da fiscalização.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function createPeriodo() 
    {   
        $this->authorize('create', auth()->user());

        try{
            $dados = $this->service->getService('Fiscalizacao')->view();
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para criar o período da fiscalização.");
        }

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function storePeriodo(PeriodoFiscalizacaoRequest $request)
    {
        $this->authorize('create', auth()->user());

        try{
            $validated = $request->validated();
            $this->service->getService('Fiscalizacao')->save($validated, $this->service);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar o período da fiscalização.");
        }

        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>O ano foi criado com sucesso')
            ->with('class', 'alert-success');
    }

    public function updateStatus(/*Request $request*/$id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $this->service->getService('Fiscalizacao')->updateStatus($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status do período da fiscalização.");
        }
        
        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>Status do período com a ID: ' . $id . ' foi atualizado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function editPeriodo($id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $dados = $this->service->getService('Fiscalizacao')->view($id);
            $variaveis = $dados['variaveis'];
            $resultado = $dados['resultado'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para editar o período da fiscalização.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }


    public function updatePeriodo(PeriodoFiscalizacaoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Fiscalizacao')->save($validated, null, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao editar os dados do período da fiscalização.");
        }

        $message = isset($erro['message']) ? $erro['message'] : '<i class="icon fa fa-check"></i>O período com a ID: ' . $id . ' foi editado com sucesso';
        $class = isset($erro['class']) ? $erro['class'] : 'alert-success';

        return redirect()->route('fiscalizacao.index')
            ->with('message', $message)
            ->with('class', $class);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Fiscalizacao')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em fiscalização.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function mostrarMapa()
    {
        try{
            $dados = $this->service->getService('Fiscalizacao')->mapaSite();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o mapa da fiscalização.");
        }

        return view('site.mapa-fiscalizacao', $dados);
    }

    public function mostrarMapaPeriodo($id)
    {
        try{
            $dados = $this->service->getService('Fiscalizacao')->mapaSite($id);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Período não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o mapa da fiscalização.");
        }

        return view('site.mapa-fiscalizacao', $dados);
    }
}
