<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Concurso;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;

class ConcursoController extends Controller
{
    // Nome da classe
    private $class = 'ConcursoController';
    // Variáveis extras da página
    public $variaveis = [
        'singular' => 'concurso',
        'singulariza' => 'o concurso',
        'plural' => 'concursos',
        'pluraliza' => 'concursos',
        'titulo_criar' => 'Cadastrar concurso',
        'btn_criar' => '<a href="/admin/concursos/criar" class="btn btn-primary mr-1">Novo Concurso</a>',
        'btn_lixeira' => '<a href="/admin/concursos/lixeira" class="btn btn-warning">Concursos Deletados</a>',
        'btn_lista' => '<a href="/admin/concursos" class="btn btn-primary">Lista de Concursos</a>',
        'titulo' => 'Concursos Deletados',
    ];

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function resultados()
    {
        $resultados = Concurso::orderBy('idconcurso','DESC')->paginate(10);
        return $resultados;
    }
    
    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Modalidade',
            'Nº do Processo',
            'Situação',
            'Data de Realização',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/concurso/'.$resultado->idconcurso.'" class="btn btn-sm btn-default">Ver</a> ';
            if(ControleController::mostra($this->class, 'edit'))
                $acoes .= '<a href="/admin/concursos/editar/'.$resultado->idconcurso.'" class="btn btn-sm btn-primary">Editar</a> ';
            if(ControleController::mostra($this->class, 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/concursos/apagar/'.$resultado->idconcurso.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir o concurso?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->idconcurso,
                $resultado->modalidade,
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

    public function create()
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        ControleController::autoriza($this->class, 'create');
        $regras = [
            'modalidade' => 'required|max:191',
            'titulo' => 'required|max:191',
            'nrprocesso' => 'required|max:191|unique:concursos',
            'situacao' => 'required|max:191',
            'datarealizacao' => 'required',
            'objeto' => 'required',
            'linkexterno' => 'max:191'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'nrprocesso.unique' => 'Já existe um concurso com este nº de processo',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        $datarealizacao = Helper::retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        // Update nos dados do BD
        $concurso = new Concurso();
        $concurso->modalidade = $request->input('modalidade');
        $concurso->titulo = $request->input('titulo');
        $concurso->nrprocesso = $request->input('nrprocesso');
        $concurso->situacao = $request->input('situacao');
        $concurso->datarealizacao = $datarealizacao;
        $concurso->objeto = $request->input('objeto');
        $concurso->linkexterno = $request->input('linkexterno');
        $concurso->idusuario = $request->input('idusuario');
        $save = $concurso->save();
        if(!$save)
            abort(500);
        event(new CrudEvent('concurso', 'criou', $concurso->idconcurso));
        return redirect()->route('concursos.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Concurso cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = Concurso::find($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $regras = [
            'modalidade' => 'required|max:191',
            'titulo' => 'required|max:191',
            'nrprocesso' => 'required|max:191',
            'situacao' => 'required|max:191',
            'datarealizacao' => 'required',
            'objeto' => 'required',
            'linkexterno' => 'max:191'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        $datarealizacao = Helper::retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        // Update nos dados do BD
        $concurso = Concurso::find($id);
        $concurso->modalidade = $request->input('modalidade');
        $concurso->titulo = $request->input('titulo');
        $concurso->nrprocesso = $request->input('nrprocesso');
        $concurso->situacao = $request->input('situacao');
        $concurso->datarealizacao = $datarealizacao;
        $concurso->objeto = $request->input('objeto');
        $concurso->linkexterno = $request->input('linkexterno');
        $concurso->idusuario = $request->input('idusuario');
        $update = $concurso->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('concurso', 'editou', $concurso->idconcurso));
        return redirect()->route('concursos.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Concurso editado com sucesso!')
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
        $concurso = Concurso::find($id);
        $delete = $concurso->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('concurso', 'apagou', $concurso->idconcurso));
        return redirect()->route('concursos.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Concurso deletado com sucesso!')
            ->with('class', 'alert-danger');
    }

    /**
     * Mostra a lixeira de concursos
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira()
    {
        ControleController::autorizaStatic(['1']);
        $resultados = Concurso::onlyTrashed()->paginate(10);
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Modalidade',
            'Nº do Processo',
            'Deletado em',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/concursos/restore/'.$resultado->idconcurso.'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idconcurso,
                $resultado->modalidade,
                $resultado->nrprocesso,
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
        $concurso = Concurso::onlyTrashed()->find($id);
        $restore = $concurso->restore();
        if(!$restore)
            abort(500);
        event(new CrudEvent('concurso', 'restaurou', $concurso->idconcurso));
        return redirect()->route('concursos.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Concurso restaurado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        ControleController::autoriza($this->class, 'index');
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Concurso::where('modalidade','LIKE','%'.$busca.'%')
            ->orWhere('nrprocesso','LIKE','%'.$busca.'%')
            ->orWhere('situacao','LIKE','%'.$busca.'%')
            ->orWhere('objeto','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
