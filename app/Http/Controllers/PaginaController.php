<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Pagina;
use App\User;
use App\PaginaCategoria;
use Illuminate\Support\Str;
use App\Http\Controllers\ControleController;

class PaginaController extends Controller
{
    public $variaveis = [
        'singular' => 'pagina',
        'singulariza' => 'a página',
        'plural' => 'paginas',
        'pluraliza' => 'páginas',
        'titulo_criar' => 'Criar página',
        'btn_criar' => '<a href="/admin/paginas/criar" class="btn btn-primary mr-1">Nova Página</a>',
        'btn_lixeira' => '<a href="/admin/paginas/lixeira" class="btn btn-warning">Páginas Deletadas</a>',
        'btn_lista' => '<a href="/admin/paginas" class="btn btn-primary">Lista de Páginas</a>',
        'titulo' => 'Páginas Deletadas'
    ];

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'showSemCategoria']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function resultados()
    {
        $resultados = Pagina::orderBy('idpagina','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Título',
            'Categoria',
            'Última alteração',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            if(isset($resultado->paginacategoria->nome))
                $acao_um = '<a href="/'.Helper::toSlug($resultado->paginacategoria->nome).'/'.$resultado->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a>';
            else
                $acao_um = '<a href="/'.$resultado->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a>';
            $acoes = $acao_um.' ';
            $acoes .= '<a href="/admin/paginas/editar/'.$resultado->idpagina.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/paginas/apagar/'.$resultado->idpagina.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a notícia?\')" />';
            $acoes .= '</form>';
            if(isset($resultado->paginacategoria->nome))
                $categoria = $resultado->paginacategoria->nome;
            else
                $categoria = 'Sem Categoria';
            $conteudo = [
                $resultado->idpagina,
                $resultado->titulo,
                $categoria,
                Helper::formataData($resultado->updated_at).'<br><small>Por: '.$resultado->user->nome.'</small>',
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

    public function index()
    {
        ControleController::autorizacao([
            'Admin',
            'Editor',
            'Procuradoria'        
        ]);
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
    public function create()
    {
        ControleController::autorizacao([
            'Admin',
            'Editor',
            'Procuradoria'        
        ]);
        $categorias = PaginaCategoria::all();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('categorias', 'variaveis'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ControleController::autorizacao([
            'Admin',
            'Editor',
            'Procuradoria'        
        ]);
        $regras = [
            'titulo' => 'required',
            'conteudo' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);

        $pagina = new Pagina();
        $pagina->titulo = $request->input('titulo');
        $pagina->subtitulo = $request->input('subtitulo');
        $pagina->slug = Str::slug($request->input('titulo'), '-');
        $pagina->img = $request->input('img');
        $pagina->conteudo = $request->input('conteudo');
        $pagina->idcategoria = $request->input('categoria');
        $pagina->idusuario = $request->input('idusuario');
        $save = $pagina->save();
        if(!$save)
            abort(500);
        return redirect('/admin/paginas')
            ->with('message', '<i class="icon fa fa-check"></i>Página criada com sucesso!')
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
        ControleController::autorizacao([
            'Admin',
            'Editor',
            'Procuradoria'        
        ]);
        $resultado = Pagina::find($id);
        $categorias = PaginaCategoria::all();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'categorias', 'variaveis'));
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
        ControleController::autorizacao([
            'Admin',
            'Editor',
            'Procuradoria'        
        ]);
        $regras = [
            'titulo' => 'required',
            'conteudo' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório'
        ];
        $erros = $request->validate($regras, $mensagens);
        $pagina = Pagina::find($id);
        $pagina->titulo = $request->input('titulo');
        $pagina->subtitulo = $request->input('subtitulo');
        $pagina->slug = Str::slug($request->input('titulo'), '-');
        $pagina->img = $request->input('img');
        $pagina->conteudo = $request->input('conteudo');
        $pagina->idcategoria = $request->input('categoria');
        $pagina->idusuario = $request->input('idusuario');
        $update = $pagina->update();
        if(!$update)
            abort(500);
        return redirect('/admin/paginas')
            ->with('message', '<i class="icon fa fa-check"></i>Página editada com sucesso!')
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
        ControleController::autorizacao([
            'Admin',
            'Editor',
            'Procuradoria'        
        ]);
        $pagina = Pagina::find($id);
        $delete = $pagina->delete();
        if(!$delete)
            abort(500);
        return redirect('/admin/paginas')
            ->with('message', '<i class="icon fa fa-ban"></i>Página deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    /**
     * Mostra a lixeira de páginas
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira()
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $resultados = Pagina::onlyTrashed()->paginate(10);
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Título',
            'Deletada em:',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/paginas/restore/'.$resultado->idpagina.'" class="btn btn-sm btn-primary">Restaurar</a>';
            $conteudo = [
                $resultado->idpagina,
                $resultado->titulo,
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
     * Restaura página deletada
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        ControleController::autorizacao([
            'Admin'
        ]);
        $pagina = Pagina::onlyTrashed()->find($id);
        $pagina->restore();
        return redirect('/admin/paginas')
            ->with('message', '<i class="icon fa fa-check"></i>Página restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        ControleController::autorizacao([
            'Admin',
            'Editor',
            'Procuradoria'        
        ]);
        $busca = Input::get('q');
        $variaveis = (object) $this->variaveis;
        $resultados = Pagina::where('titulo','LIKE','%'.$busca.'%')
            ->orWhere('conteudo','LIKE','%'.$busca.'%')
            ->paginate(10);
        $tabela = $this->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'variaveis', 'tabela', 'busca'));
    }

}
