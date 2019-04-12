<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\PaginaCategoria;

class PaginaCategoriaController extends Controller
{
    public $variaveis = [
        'singular' => 'categoria',
        'singulzariza' => 'a categoria',
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

    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
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
        $request->user()->autorizarPerfis(['admin', 'editor']);
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
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regras = [
            'nome' => 'required'
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório'
        ];
        $request->validate($regras, $mensagens);
        $categoria = new PaginaCategoria();
        $categoria->nome = $request->input('nome');
        $categoria->idusuario = $request->input('idusuario');
        $save = $categoria->save();
        if(!$save)
            abort(500);
        return redirect('/admin/paginas/categorias');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $resultado = PaginaCategoria::find($id);
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
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regras = [
            'nome' => 'required'
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório'
        ];
        $request->validate($regras, $mensagens);
        $categoria = PaginaCategoria::find($id);
        $categoria->nome = $request->input('nome');
        $categoria->idusuario = $request->input('idusuario');
        $update = $categoria->update();
        if(!$update)
            abort(500);
        return redirect('/admin/paginas/categorias');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $pagina = PaginaCategoria::find($id);
        $delete = $pagina->delete();
        if(!$delete)
            abort(500);
        return redirect('/admin/paginas/categorias');
    }

    public function busca()
    {
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = PaginaCategoria::where('nome','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
