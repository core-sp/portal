<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PaginaCategoria;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PaginaCategoriaController extends Controller
{
    // Nome da classe Pai (em relação à controle)
    private $parent = 'PaginaController';
    // Variáveis
    public $variaveis = [
        'singular' => 'categoria',
        'singulariza' => 'a categoria',
        'plural' => 'categorias',
        'pluraliza' => 'categorias',
        'titulo_criar' => 'Criar categoria',
        'form' => 'paginacategoria',
        'busca' => 'paginas/categorias',
        'btn_criar' => '<a href="/admin/paginas/categorias/criar" class="btn btn-primary">Nova Categoria</a>'
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function resultados()
    {
        $resultados = PaginaCategoria::orderBy('idpaginacategoria','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Nome',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/paginas/categorias/editar/'.$resultado->idpaginacategoria.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/paginas/categorias/apagar/'.$resultado->idpaginacategoria.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a categoria?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->idpaginacategoria,
                $resultado->nome,
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
        ControleController::autoriza($this->parent, __FUNCTION__);
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        ControleController::autoriza($this->parent, 'create');
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Request $request)
    {
        ControleController::autoriza($this->parent, 'create');
        $regras = [
            'nome' => 'required|max:191|unique:pagina_categorias'
        ];
        $mensagens = [
            'unique' => 'Já existe uma categoria com este nome',
            'required' => 'O campo :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $request->validate($regras, $mensagens);

        $save = PaginaCategoria::create(request(['nome', 'idusuario']));

        if(!$save)
            abort(500);
        event(new CrudEvent('categoria de página', 'criou', $save->idpaginacategoria));
        return redirect('/admin/paginas/categorias')
            ->with('message', '<i class="icon fa fa-check"></i>Categoria de página criada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autoriza($this->parent, __FUNCTION__);
        $resultado = PaginaCategoria::findOrFail($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        ControleController::autoriza($this->parent, 'edit');
        $regras = [
            'nome' => 'required|max:191'
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $request->validate($regras, $mensagens);

        $update = PaginaCategoria::findOrFail($id)->update(request(['nome', 'idusuario']));
    
        if(!$update)
            abort(500);
        event(new CrudEvent('categoria de página', 'editou', $id));
        return redirect('/admin/paginas/categorias')
            ->with('message', '<i class="icon fa fa-check"></i>Categoria de página editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        ControleController::autoriza($this->parent, __FUNCTION__);
        $pagina = PaginaCategoria::findOrFail($id);
        $delete = $pagina->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('categoria de página', 'apagou', $pagina->idpaginacategoria));
        return redirect('/admin/paginas/categorias')
            ->with('message', '<i class="icon fa fa-ban"></i>Categoria de página deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function busca()
    {
        ControleController::autoriza($this->parent, 'index');
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = PaginaCategoria::where('nome','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
