<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Concurso;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;

class ConcursoController extends Controller
{
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
            $acoes .= '<a href="/admin/concursos/editar/'.$resultado->idconcurso.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/concursos/apagar/'.$resultado->idconcurso.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir o concurso?\')" />';
            $acoes .= '</form>';
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
            'table-hovered'
        ];
        // Monta e retorna tabela        
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
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
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $regras = [
            'modalidade' => 'required',
            'titulo' => 'required',
            'nrprocesso' => 'required|unique:concursos',
            'situacao' => 'required',
            'datarealizacao' => 'required',
            'objeto' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'nrprocesso.unique' => 'Já existe um concurso com este nº de processo',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
        ];
        $erros = $request->validate($regras, $mensagens);

        $concurso = new Concurso();
        $concurso->modalidade = $request->input('modalidade');
        $concurso->titulo = $request->input('titulo');
        $concurso->nrprocesso = $request->input('nrprocesso');
        $concurso->situacao = $request->input('situacao');
        $concurso->datarealizacao = $request->input('datarealizacao');
        $concurso->objeto = $request->input('objeto');
        $concurso->linkexterno = $request->input('linkexterno');
        $concurso->idusuario = $request->input('idusuario');
        $save = $concurso->save();
        if(!$save)
            abort(500);
        return redirect()->route('concursos.lista');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $resultado = Concurso::find($id);
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
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $regras = [
            'modalidade' => 'required',
            'titulo' => 'required',
            'nrprocesso' => 'required',
            'situacao' => 'required',
            'datarealizacao' => 'required',
            'objeto' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
        ];
        $erros = $request->validate($regras, $mensagens);

        $concurso = Concurso::find($id);
        $concurso->modalidade = $request->input('modalidade');
        $concurso->titulo = $request->input('titulo');
        $concurso->nrprocesso = $request->input('nrprocesso');
        $concurso->situacao = $request->input('situacao');
        $concurso->datarealizacao = $request->input('datarealizacao');
        $concurso->objeto = $request->input('objeto');
        $concurso->linkexterno = $request->input('linkexterno');
        $concurso->idusuario = $request->input('idusuario');
        $update = $concurso->update();
        if(!$update)
            abort(500);
        return redirect()->route('concursos.lista');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $concurso = Concurso::find($id);
        $concurso->delete();
        return redirect()->route('concursos.lista');
    }

    /**
     * Mostra a lixeira de concursos
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
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
            'table-hovered'
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
    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $concurso = Concurso::onlyTrashed()->find($id);
        $concurso->restore();
        return redirect()->route('concursos.lista');
    }

    public function busca()
    {
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
