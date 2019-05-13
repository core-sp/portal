<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Curso;
use App\Regional;
use App\CursoInscrito;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Helpers\CursoHelper;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\CursoInscritoController;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;

class CursoController extends Controller
{
    // Nome da classe
    private $class = 'CursoController';
    // Variáveis
    public $variaveis = [
        'singular' => 'curso',
        'singulariza' => 'o curso',
        'plural' => 'cursos',
        'pluraliza' => 'cursos',
        'titulo_criar' => 'Cadastrar curso',
        'btn_criar' => '<a href="/admin/cursos/criar" class="btn btn-primary mr-1">Novo Curso</a>',
        'btn_lixeira' => '<a href="/admin/cursos/lixeira" class="btn btn-warning">Cursos Cancelados</a>',
        'btn_lista' => '<a href="/admin/cursos" class="btn btn-primary mr-1">Lista de Cursos</a>',
        'titulo' => 'Cursos cancelados',
    ];

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
    }

    public function resultados()
    {
        $resultados = Curso::orderBy('idcurso','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Turma',
            'Tipo / Tema',
            'Onde / Quando',
            'Vagas',
            'Regional',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/curso/'.$resultado->idcurso.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if(ControleController::mostra('CursoInscritoController', 'index'))
                $acoes .= '<a href="/admin/cursos/inscritos/'.$resultado->idcurso.'" class="btn btn-sm btn-secondary">Inscritos</a> ';
            if(ControleController::mostra($this->class, 'edit'))
                $acoes .= '<a href="/admin/cursos/editar/'.$resultado->idcurso.'" class="btn btn-sm btn-primary">Editar</a> ';
            if(ControleController::mostra($this->class, 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/cursos/cancelar/'.$resultado->idcurso.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar" onclick="return confirm(\'Tem certeza que deseja cancelar o curso?\')" />';
                $acoes .= '</form>';
            }
            if($resultado->publicado == 'Sim')
                $publicado = 'Publicado';
            else
                $publicado = 'Rascunho';
            $conteudo = [
                $resultado->idcurso,
                $resultado->tipo.'<br>'.$resultado->tema.'<br /><small><em>'.$publicado.'</em></small>',
                $resultado->endereco.'<br />'.Helper::formataData($resultado->datarealizacao),
                CursoHelper::contagem($resultado->idcurso).' / '.$resultado->nrvagas,
                $resultado->regional->regional,
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
        $regionais = Regional::all();
        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }

    public function store(Request $request)
    {
        ControleController::autoriza($this->class, 'create');
        $regras = [
            'tipo' => 'max:191',
            'tema' => 'required|max:191',
            'img' => 'max:191',
            'datarealizacao' => 'required',
            'horainicio' => 'required',
            'endereco' => 'required|max:191',
            'nrvagas' => 'required|numeric',
            'descricao' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'numeric' => 'O :attribute aceita apenas números',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        $datarealizacao = Helper::retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        $datatermino = Helper::retornaDateTime($request->input('datatermino'), $request->input('horatermino'));
        // Inputa dados no BD
        $curso = New Curso();
        $curso->tipo = $request->input('tipo');
        $curso->tema = $request->input('tema');
        $curso->datarealizacao = $datarealizacao;
        $curso->datatermino = $datatermino;
        $curso->endereco = $request->input('endereco');
        $curso->img = $request->input('img');
        $curso->nrvagas = $request->input('nrvagas');
        $curso->idregional = $request->input('idregional');
        $curso->descricao = $request->input('descricao');
        $curso->publicado = $request->input('publicado');
        $curso->resumo = $request->input('resumo');
        $curso->idusuario = $request->input('idusuario');
        $save = $curso->save();
        if(!$save)
            abort(500);
        event(new CrudEvent('curso', 'criou', $curso->idcurso));
        return redirect()->route('cursos.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Curso criado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = Curso::with('regional','user')->find($id);
        $regionais = Regional::all();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'regionais', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $regras = [
            'tipo' => 'max:191',
            'tema' => 'required|max:191',
            'img' => 'max:191',
            'datarealizacao' => 'required',
            'horainicio' => 'required',
            'endereco' => 'required|max:191',
            'nrvagas' => 'required|numeric',
            'descricao' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'numeric' => 'O :attribute aceita apenas números',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Formata DateTime
        $datarealizacao = Helper::retornaDateTime($request->input('datarealizacao'), $request->input('horainicio'));
        $datatermino = Helper::retornaDateTime($request->input('datatermino'), $request->input('horatermino'));
        // Update de dados no BD
        $curso = Curso::find($id);
        $curso->tipo = $request->input('tipo');
        $curso->tema = $request->input('tema');
        $curso->datarealizacao = $datarealizacao;
        $curso->datatermino = $datatermino;
        $curso->endereco = $request->input('endereco');
        $curso->img = $request->input('img');
        $curso->nrvagas = $request->input('nrvagas');
        $curso->idregional = $request->input('idregional');
        $curso->descricao = $request->input('descricao');
        $curso->resumo = $request->input('resumo');
        $curso->publicado = $request->input('publicado');
        $curso->idusuario = $request->input('idusuario');
        $update = $curso->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('curso', 'editou', $curso->idcurso));
        return redirect()->route('cursos.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Curso editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $curso = Curso::find($id);
        $delete = $curso->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('curso', 'cancelou', $curso->idcurso));
        return redirect()->route('cursos.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Curso cancelado com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function lixeira()
    {
        ControleController::autorizaStatic(['1']);
        $resultados = Curso::onlyTrashed()->paginate(10);
        // Opções de cabeçalho da tabela
        $headers = [
            'Turma',
            'Tipo / Tema',
            'Onde / Quando',
            'Regional',
            'Cancelado em:',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/cursos/restore/'.$resultado->idcurso.'" class="btn btn-sm btn-primary">Restaurar</a> ';
            $conteudo = [
                $resultado->idcurso,
                $resultado->tipo.'<br>'.$resultado->tema,
                $resultado->endereco.'<br />'.Helper::formataData($resultado->datarealizacao),
                $resultado->regional->regional,
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
        // Monta e retorna tabela
        $variaveis = (object) $this->variaveis; 
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        ControleController::autorizaStatic(['1']);
        $curso = Curso::onlyTrashed()->find($id);
        $restore = $curso->restore();
        if(!$restore)
            abort(500);
        event(new CrudEvent('curso', 'reabriu', $curso->idcurso));
        return redirect()->route('cursos.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Curso restaurado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function inscritos($id)
    {
        ControleController::autoriza('CursoInscritoController', 'index');
        $resultados = CursoInscrito::where('idcurso', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $curso = Curso::find($id);
        if(!$curso)
            abort(500);
        $variaveis = [
            'pluraliza' => 'inscritos',
            'plural' => 'inscritos',
            'singular' => 'inscrito',
            'singulariza' => 'o inscrito',
            'continuacao_titulo' => 'em '.$curso->tipo.': '.$curso->tema,
            'btn_criar' => '<a href="/admin/cursos/adicionar-inscrito/'.$curso->idcurso.'" class="btn btn-primary mr-1">Adicionar inscrito</a> ',
            'btn_lixeira' => '<a href="/admin/cursos" class="btn btn-default">Lista de Cursos</a>'
        ];
        if(!ControleController::mostra('CursoInscritoController', 'create'))
            unset($variaveis['btn_criar']);
        $tabela = CursoInscritoController::tabelaCompleta($resultados);
        $variaveis = (object) $variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        ControleController::autoriza('CursoInscritoController', 'index');
        $busca = Input::get('q');
        $cursos = Curso::where('tipo','LIKE','%'.$busca.'%')
            ->orWhere('tema','LIKE','%'.$busca.'%')
            ->orWhere('descricao','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($cursos) > 0) 
            return view('admin.cursos.home', compact('cursos', 'busca'));
        else
            return view('admin.cursos.home')->withMessage('Nenhum curso encontrado');
    }
}
