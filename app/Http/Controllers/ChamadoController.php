<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Chamado;
use App\Http\Controllers\Helper;
use App\Http\Controllers\ControleController;

class ChamadoController extends Controller
{
    // Variáveis extras de chamado
    public $variaveis = [
        'singular' => 'chamado',
        'singulariza' => 'o chamado',
        'plural' => 'chamados',
        'pluraliza' => 'chamados',
        'titulo_criar' => 'Registrar chamado',
        'btn_lista' => '<a href="/admin/chamados" class="btn btn-primary">Lista de Chamados</a>',
        'btn_lixeira' => '<a href="/admin/chamados/concluidos" class="btn btn-warning">Chamados Concluídos</a>',
        'titulo' => 'Chamados concluídos'
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function resultados()
    {
        $resultados = Chamado::orderBy('created_at','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Tipo',
            'Prioridade',
            'Usuário',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/chamados/ver/'.$resultado->idchamado.'" class="btn btn-sm btn-default">Ver</a> ';
            $acoes .= '<form method="POST" action="/admin/chamados/apagar/'.$resultado->idchamado.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-success" value="Dar baixa" onclick="return confirm(\'Tem certeza que deseja dar baixa no chamado?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->idchamado,
                $resultado->tipo,
                $resultado->prioridade,
                $resultado->user->nome,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function index(Request $request)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        $regras = [
            'tipo' => 'required',
            'prioridade' => 'required',
            'mensagem' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);
        // Inputa dados no BD
        $chamado = new Chamado();
        $chamado->tipo = $request->input('tipo');
        $chamado->prioridade = $request->input('prioridade');
        $chamado->mensagem = $request->input('mensagem');
        $chamado->img = $request->input('img');
        $chamado->idusuario = $request->input('idusuario');
        $save = $chamado->save();
        if(!$save)
            abort(500);
        return redirect('/admin/perfil')
            ->with('message', '<i class="icon fa fa-check"></i>Chamado registrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show(Request $request, $id)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $resultado = Chamado::withTrashed()->find($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.mostra', compact('variaveis', 'resultado'));
    }

    public function destroy(Request $request, $id)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $resultado = Chamado::find($id);
        $delete = $resultado->delete();
        if(!$delete)
            abort(500);
        return redirect('/admin/chamados')
            ->with('message', '<i class="icon fa fa-check"></i>Chamado concluído com sucesso!')
            ->with('class', 'alert-success');
    }

    public function lixeira(Request $request)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $resultados = Chamado::onlyTrashed()->paginate(10);
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Tipo',
            'Usuário',
            'Concluído em:',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/chamados/ver/'.$resultado->idchamado.'" class="btn btn-sm btn-default">Ver</a> ';
            $acoes .= '<a href="/admin/chamados/restore/'.$resultado->idchamado.'" class="btn btn-sm btn-primary">Reabrir</a>';
            $conteudo = [
                $resultado->idchamado,
                $resultado->tipo,
                $resultado->user->nome,
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

    public function restore(Request $request, $id)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $chamado = Chamado::onlyTrashed()->find($id);
        $chamado->restore();
        return redirect('/admin/chamados')
            ->with('message', '<i class="icon fa fa-check"></i>Chamado reaberto!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Chamado::where('tipo','LIKE','%'.$busca.'%')
            ->orWhere('prioridade','LIKE','%'.$busca.'%')
            ->orWhere('mensagem','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
