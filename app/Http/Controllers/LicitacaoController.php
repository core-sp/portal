<?php

namespace App\Http\Controllers;

use App\Licitacao;
use App\Events\CrudEvent;
use App\Http\Requests\LicitacaoRequest;
use App\Repositories\LicitacaoRepository;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class LicitacaoController extends Controller
{
    use ControleAcesso;

    private $class = 'LicitacaoController';
    private $licitacaoModel;
    private $licitacaoRepository;
    private $variaveis;

    public function __construct(Licitacao $licitacao, LicitacaoRepository $licitacaoRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid', 'siteBusca']]);
        $this->licitacaoModel = $licitacao;
        $this->licitacaoRepository = $licitacaoRepository;
        $this->variaveis = $licitacao->variaveis();
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultados = $this->licitacaoRepository->getToTable();
        $tabela = $this->licitacaoModel->tabelaCompleta($resultados);
        if(!$this->mostra($this->class, 'create'))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(LicitacaoRequest $request)
    {
        $request->validated();
        
        $save = $this->licitacaoRepository->store($request);
        if(!$save)
            abort(500);

        event(new CrudEvent('licitação', 'criou', $save->idlicitacao));
        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultado = $this->licitacaoRepository->findById($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(LicitacaoRequest $request, $id)
    {
        $request->validated();
        
        $update = $this->licitacaoRepository->update($id, $request);
        if(!$update)
            abort(500);

        event(new CrudEvent('licitação', 'editou', $id));
        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($id)
    {
        $licitacao = $this->licitacaoRepository->findById($id);
        return response()
            ->view('site.licitacao', compact('licitacao'))
            ->header('Cache-Control','no-cache');
    }

    public function destroy($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        
        $delete = $this->licitacaoRepository->findById($id)->delete();
        if(!$delete)
            abort(500);
        
        event(new CrudEvent('licitação', 'apagou', $id));
        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-danger"></i>Licitação deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function lixeira()
    {
        $this->autorizaStatic(['1']);
        $variaveis = (object) $this->variaveis;
        $resultados = $this->licitacaoRepository->getTrashed();
        $tabela = $this->licitacaoModel->tabelaTrashed($resultados);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->autorizaStatic(['1']);
        
        $restore = $this->licitacaoRepository->getTrashedById($id)->restore();
        if(!$restore)
            abort(500);
        
        event(new CrudEvent('licitação', 'restaurou', $id));
        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->licitacaoRepository->getBusca($busca);
        $tabela = $this->licitacaoModel->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteGrid()
    {
        $licitacoes = $this->licitacaoRepository->getSiteGrid();
        return response()
            ->view('site.licitacoes', compact('licitacoes'))
            ->header('Cache-Control','no-cache');
    }

    public function siteBusca()
    {
        // Refatorar
        $licitacoes = $this->licitacaoRepository->getBuscaSite();
        if (!empty(IlluminateRequest::input('palavra-chave'))
            or !empty(IlluminateRequest::input('modalidade')) 
            or !empty(IlluminateRequest::input('situacao')) 
            or !empty(IlluminateRequest::input('nrlicitacao'))
            or !empty(IlluminateRequest::input('nrprocesso'))
            or !empty(IlluminateRequest::input('datarealizacao'))
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        if (count($licitacoes) > 0) {
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        } else {
            $licitacoes = null;
            return view('site.licitacoes', compact('licitacoes', 'busca'));
        }
    }
}
