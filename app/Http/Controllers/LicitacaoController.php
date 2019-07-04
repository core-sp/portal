<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Licitacao;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;

class LicitacaoController extends Controller
{
    // Nome da classe
    private $class = 'LicitacaoController';
    // Variáveis extras da página
    public $variaveis = [
        'singular' => 'licitacao',
        'singulariza' => 'a licitação',
        'plural' => 'licitacoes',
        'pluraliza' => 'licitações',
        'titulo_criar' => 'Cadastrar licitação',
        'btn_criar' => '<a href="/admin/licitacoes/criar" class="btn btn-primary mr-1">Nova Licitação</a>',
        'btn_lixeira' => '<a href="/admin/licitacoes/lixeira" class="btn btn-warning">Licitações Deletadas</a>',
        'btn_lista' => '<a href="/admin/licitacoes" class="btn btn-primary mr-1">Lista de Licitações</a>',
        'titulo' => 'Licitações Deletadas',
    ];

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'buscaAvancada']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function resultados()
    {
        $resultados = Licitacao::orderBy('idlicitacao','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Modalidade',
            'Nº da Licitação',
            'Nº do Processo',
            'Situação',
            'Data de Realização',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/licitacao/'.$resultado->idlicitacao.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if(ControleController::mostra($this->class, 'edit'))
                $acoes .= '<a href="/admin/licitacoes/editar/'.$resultado->idlicitacao.'" class="btn btn-sm btn-primary">Editar</a> ';
            if(ControleController::mostra($this->class, 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/licitacoes/apagar/'.$resultado->idlicitacao.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a licitação?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->idlicitacao,
                $resultado->modalidade,
                $resultado->nrlicitacao,
                $resultado->nrprocesso,
                $resultado->situacao,
                Helper::formataData($resultado->datarealizacao),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        // Monta e retorna tabela        
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function index()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        if(!ControleController::mostra($this->class, 'create'))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ControleController::autoriza($this->class, 'create');
        $regras = [
            'modalidade' => 'required|max:191',
            'titulo' => 'max:191',
            'nrlicitacao' => 'required|max:191',
            'nrprocesso' => 'required|max:191',
            'situacao' => 'required|max:191',
            'objeto' => 'required',
            'datarealizacao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrlicitacao.required' => 'O nº da licitação é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        $datarealizacao = Helper::retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        // Inputa no BD
        $licitacao = new Licitacao();
        $licitacao->modalidade = $request->input('modalidade');
        $licitacao->uasg = $request->input('uasg');
        $licitacao->edital = $request->input('edital');
        $licitacao->nrlicitacao = $request->input('nrlicitacao');
        $licitacao->titulo = $request->input('titulo');
        $licitacao->nrprocesso = $request->input('nrprocesso');
        $licitacao->situacao = $request->input('situacao');
        $licitacao->datarealizacao = $datarealizacao;
        $licitacao->objeto = $request->input('objeto');
        $licitacao->idusuario = $request->input('idusuario');
        $save = $licitacao->save();
        if(!$save)
            abort(500);
        event(new CrudEvent('licitação', 'criou', $licitacao->idlicitacao));
        return redirect()->route('licitacoes.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = Licitacao::findOrFail($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $regras = [
            'modalidade' => 'required|max:191',
            'titulo' => 'max:191',
            'nrlicitacao' => 'required|max:191',
            'nrprocesso' => 'required|max:191',
            'situacao' => 'required|max:191',
            'objeto' => 'required',
            'datarealizacao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrlicitacao.required' => 'O nº da licitação é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        $datarealizacao = Helper::retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        // Update nos dados do BD        
        $licitacao = Licitacao::findOrFail($id);
        $licitacao->modalidade = $request->input('modalidade');
        $licitacao->uasg = $request->input('uasg');
        $licitacao->edital = $request->input('edital');
        $licitacao->nrlicitacao = $request->input('nrlicitacao');
        $licitacao->nrprocesso = $request->input('nrprocesso');
        $licitacao->situacao = $request->input('situacao');
        $licitacao->titulo = $request->input('titulo');
        $licitacao->datarealizacao = $datarealizacao;
        $licitacao->objeto = $request->input('objeto');
        $licitacao->idusuario = $request->input('idusuario');
        $update = $licitacao->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('licitação', 'editou', $licitacao->idlicitacao));
        return redirect()->route('licitacoes.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação editada com sucesso!')
            ->with('class', 'alert-success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $licitacao = Licitacao::findOrFail($id);
        $delete = $licitacao->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('licitação', 'apagou', $licitacao->idlicitacao));
        return redirect()->route('licitacoes.lista')
            ->with('message', '<i class="icon fa fa-danger"></i>Licitação deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    /**
     * Mostra a lixeira de licitações
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira()
    {
        ControleController::autorizaStatic(['1']);
        $resultados = Licitacao::onlyTrashed()->paginate(10);
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Modalidade',
            'Nº da Licitação',
            'Deletada em:',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/licitacoes/restore/'.$resultado->idlicitacao.'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idlicitacao,
                $resultado->modalidade,
                $resultado->nrlicitacao,
                Helper::formataData($resultado->deleted_at),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $variaveis = (object) $this->variaveis;
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    /**
     * Restaura licitação deletada
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        ControleController::autorizaStatic(['1']);
        $licitacao = Licitacao::onlyTrashed()->findOrFail($id);
        $restore = $licitacao->restore();
        if(!$restore)
            abort(500);
        event(new CrudEvent('licitação', 'restaurou', $licitacao->idlicitacao));
        return redirect()->route('licitacoes.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Licitação restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Licitacao::where('modalidade','LIKE','%'.$busca.'%')
            ->orWhere('nrlicitacao','LIKE','%'.$busca.'%')
            ->orWhere('nrprocesso','LIKE','%'.$busca.'%')
            ->orWhere('situacao','LIKE','%'.$busca.'%')
            ->orWhere('objeto','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
