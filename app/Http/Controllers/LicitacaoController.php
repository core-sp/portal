<?php

namespace App\Http\Controllers;

use App\Licitacao;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Traits\ControleAcesso;
use App\Http\Requests\LicitacaoRequest;
use App\Repositories\LicitacaoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class LicitacaoController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    private $class = 'LicitacaoController';
    private $licitacaoRepository;
    private $variaveis;

    public function __construct(LicitacaoRepository $licitacaoRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid', 'siteBusca']]);
        $this->licitacaoRepository = $licitacaoRepository;
        $this->variaveis = [
            'singular' => 'licitacao',
            'singulariza' => 'a licitação',
            'plural' => 'licitacoes',
            'pluraliza' => 'licitações',
            'titulo_criar' => 'Cadastrar licitação',
            'btn_criar' => '<a href="'.route('licitacoes.create').'" class="btn btn-primary mr-1">Nova Licitação</a>',
            'btn_lixeira' => '<a href="'.route('licitacoes.trashed').'" class="btn btn-warning">Licitações Deletadas</a>',
            'btn_lista' => '<a href="'.route('licitacoes.index').'" class="btn btn-primary mr-1">Lista de Licitações</a>',
            'titulo' => 'Licitações Deletadas'
        ];
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->licitacaoRepository->getToTable();
        $tabela = $this->tabelaCompleta($resultados);

        if(!$this->mostra($this->class, 'create')) {
            unset($this->variaveis['btn_criar']);
        }
            
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $variaveis = (object) $this->variaveis;
        $modalidades = Licitacao::modalidadesLicitacao();
        $situacoes = Licitacao::situacoesLicitacao();

        return view('admin.crud.criar', compact('variaveis', 'modalidades', 'situacoes'));
    }

    public function store(LicitacaoRequest $request)
    {
        $request->validated();
        
        $save = $this->licitacaoRepository->store($request);

        if(!$save) {
            abort(500);
        }

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
        $modalidades = Licitacao::modalidadesLicitacao();
        $situacoes = Licitacao::situacoesLicitacao();

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'modalidades', 'situacoes'));
    }

    public function update(LicitacaoRequest $request, $id)
    {
        $request->validated();
        
        $update = $this->licitacaoRepository->update($id, $request);

        if(!$update) {
            abort(500);
        }
            
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

        if(!$delete) {
            abort(500);
        }
            
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
        $tabela = $this->tabelaTrashed($resultados);

        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->autorizaStatic(['1']);
        
        $restore = $this->licitacaoRepository->getTrashedById($id)->restore();

        if(!$restore) {
            abort(500);
        }
           
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
        $tabela = $this->tabelaCompleta($resultados);

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteGrid()
    {
        $licitacoes = $this->licitacaoRepository->getSiteGrid();
        $modalidades = Licitacao::modalidadesLicitacao();
        $situacoes = Licitacao::situacoesLicitacao();

        return response()
            ->view('site.licitacoes', compact('licitacoes', 'modalidades', 'situacoes'))
            ->header('Cache-Control','no-cache');
    }

    public function siteBusca(Request $request)
    {
        $buscaDia = $request->datarealizacao;

        // Se nenhum critério foi fornecido, chama método que abre a tela inical de busca
        if(empty($request->palavrachave) && empty($request->modalidade) && empty($request->situacao) && empty($request->nrlicitacao) && empty($request->nrprocesso) && empty($request->datarealizacao)) {
            $this->siteGrid();
        }

        if(isset($buscaDia)) {
            $diaArray = explode('/', $buscaDia);
            $checaDia = (count($diaArray) != 3 || $diaArray[2] == null)  ? false : checkdate($diaArray[1], $diaArray[0], $diaArray[2]);

            if($checaDia == false) {
                $licitacoes = null;

                return view('site.licitacoes', compact('licitacoes'))
                    ->with('erro', 'Data fornecida é inválida');
            }

            $buscaDia = date('Y-m-d', strtotime(str_replace('/', '-', $buscaDia)));
        }

        $licitacoes = $this->licitacaoRepository->getBuscaSite($request->palavrachave, $request->modalidade, $request->situacao, $request->nrlicitacao, $request->nrprocesso, $buscaDia);

        $busca = true;

        if (count($licitacoes) == 0) {
            $licitacoes = null;
        } 

        $modalidades = Licitacao::modalidadesLicitacao();
        $situacoes = Licitacao::situacoesLicitacao();

        return view('site.licitacoes', compact('licitacoes', 'busca', 'modalidades', 'situacoes'));
    }

    protected function tabelaHeaders()
    {
        return [
            'Código',
            'Modalidade',
            'Nº da Licitação',
            'Nº do Processo',
            'Situação',
            'Data de Realização',
            'Ações'
        ];
    }

    protected function tabelaContents($query)
    {
        return $query->map(function($row){
            $acoes = '<a href="/licitacao/'.$row->idlicitacao.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($this->mostra('LicitacaoController', 'edit'))
                $acoes .= '<a href="'.route('licitacoes.edit', $row->idlicitacao).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($this->mostra('LicitacaoController', 'destroy')) {
                $acoes .= '<form method="POST" action="'.route('licitacoes.destroy', $row->idlicitacao).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a licitação?\')" />';
                $acoes .= '</form>';
            }
            return [
                $row->idlicitacao,
                $row->modalidade,
                $row->nrlicitacao,
                $row->nrprocesso,
                $row->situacao,
                formataData($row->datarealizacao),
                $acoes
            ];
        })->toArray();
    }

    public function tabelaCompleta($query)
    {
        return $this->montaTabela(
            $this->tabelaHeaders(), 
            $this->tabelaContents($query),
            [ 'table', 'table-hover' ]
        );
    }

    public function tabelaTrashed($query)
    {
        $headers = ['Código', 'Modalidade', 'Nº da Licitação', 'Deletada em:', 'Ações'];
        $contents = $query->map(function($row){
            $acoes = '<a href="'.route('licitacoes.restore', $row->idlicitacao).'" class="btn btn-sm btn-primary">Restaurar</a>';
            return [
                $row->idlicitacao,
                $row->modalidade,
                $row->nrlicitacao,
                formataData($row->deleted_at),
                $acoes
            ];
        })->toArray();

        return $this->montaTabela(
            $headers, 
            $contents,
            [ 'table', 'table-hover' ]
        );
    }
}