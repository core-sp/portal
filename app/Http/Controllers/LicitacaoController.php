<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Licitacao;
use App\Http\Controllers\CrudController;
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
        $this->middleware('auth', ['except' => ['show', 'buscaAvancada']]);
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
        
        $datarealizacao = retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        
        $save = Licitacao::create([
            'modalidade' => request('modalidade'),
            'uasg' => request('uasg'),
            'edital' => request('edital'),
            'titulo' => request('titulo'),
            'nrlicitacao' => request('nrlicitacao'),
            'nrprocesso' => request('nrprocesso'),
            'situacao' => request('situacao'),
            'datarealizacao' => $datarealizacao,
            'objeto' => request('objeto'),
            'idusuario' => request('idusuario')
        ]);

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
        
        $datarealizacao = retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        
        $update = Licitacao::findOrFail($id)->update([
            'modalidade' => request('modalidade'),
            'uasg' => request('uasg'),
            'edital' => request('edital'),
            'titulo' => request('titulo'),
            'nrlicitacao' => request('nrlicitacao'),
            'nrprocesso' => request('nrprocesso'),
            'situacao' => request('situacao'),
            'datarealizacao' => $datarealizacao,
            'objeto' => request('objeto'),
            'idusuario' => request('idusuario')
        ]);

        if(!$update)
            abort(500);
        event(new CrudEvent('licitação', 'editou', $id));
        return redirect()->route('licitacoes.index')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $licitacao = $this->licitacaoRepository->findById($id);
        $delete = $licitacao->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('licitação', 'apagou', $licitacao->idlicitacao));
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
        $licitacao = $this->licitacaoRepository->getTrashedById($id);
        $restore = $licitacao->restore();
        if(!$restore)
            abort(500);
        event(new CrudEvent('licitação', 'restaurou', $licitacao->idlicitacao));
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
}
