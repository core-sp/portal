<?php

namespace App\Http\Controllers;

use App\Events\CrudEvent;
use App\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    // Nome da classe
    private $class = 'PostsController';
    // Variáveis
    public $variaveis = [
        'singular' => 'post',
        'singulariza' => 'o post',
        'plural' => 'posts',
        'pluraliza' => 'posts',
        'titulo_criar' => 'Cadastrar post',
        'btn_criar' => '<a href="/admin/posts/create" class="btn btn-primary mr-1">Novo Post</a>'
    ];

    public function resultados()
    {
        $resultados = Post::orderBy('id','DESC')->paginate(10);
        return $resultados;
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Autor',
            'Título',
            'Subtítulo',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/blog/'.$resultado->slug.'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if(auth()->user()->isAdmin() || auth()->user()->isEditor()) {
                $acoes .= '<a href="/admin/posts/'.$resultado->id.'/edit" class="btn btn-sm btn-primary">Editar</a> ';
                $acoes .= '<form method="POST" action="/admin/posts/'.$resultado->id.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Deletar" onclick="return confirm(\'Tem certeza que deseja deletar o post?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->id,
                $resultado->user->nome.'<br>'.formataData($resultado->created_at),
                $resultado->titulo,
                $resultado->subtitulo,
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
        $this->authorize('create', new Post());
        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    protected function validateRequest($id = null)
    {
        return request()->validate([
            'idusuario' => 'required|integer',
            'titulo' => 'required|unique:posts,titulo,'.$id,
            'subtitulo' => 'required',
            'img' => 'required',
            'conteudo' => 'required'
        ], [
            'required' => 'Este campo é obrigatório',
            'unique' => 'Já existe uma matéria com este mesmo nome'
        ]);
    }

    public function create(Post $post)
    {
        $this->authorize('create', $post);

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(Post $post)
    {
        $this->authorize('create', $post);

        $array = $this->validateRequest();

        $array['slug'] = str_slug(request('titulo'));

        $save = $post->create($array);

        event(new CrudEvent('post', 'criou', $save->id));

        return redirect('/admin/posts')
            ->with('message', '<i class="icon fa fa-check"></i>Post criado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $next = Post::select('titulo', 'slug')->where('id', '>', $post->id)->first();
        $previous = Post::select('titulo', 'slug')->where('id', '<', $post->id)->orderBy('id', 'DESC')->first();

        return view('site.post', compact('post', 'next', 'previous'));
    }

    public function blogPage()
    {
        $posts = Post::orderBy('created_at', 'DESC')->paginate(9);

        return view('site.blog', compact('posts'));
    }

    public function edit(Post $post)
    {
        $this->authorize('create', $post);

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.editar', compact('post', 'variaveis'));
    }

    public function update(Post $post)
    {
        $this->authorize('create', $post);

        $array = $this->validateRequest($post->id);

        if(!empty(request('titulo'))) {
            $array['slug'] = str_slug(request('titulo'));
        }

        $post->update($array);

        event(new CrudEvent('post', 'editou', $post->id));

        return redirect('/admin/posts')
            ->with('message', '<i class="icon fa fa-check"></i>Post editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Post $post)
    {
        $this->authorize('create', $post);

        $post->delete();

        event(new CrudEvent('post', 'apagou', $post->id));

        return redirect('/admin/posts')
            ->with('message', '<i class="icon fa fa-check"></i>Post deletado com sucesso!')
            ->with('class', 'alert-success');
    }
}
