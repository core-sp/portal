<?php

namespace App\Http\Controllers;

use App\Events\CrudEvent;
use App\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PostsController extends Controller
{
    // Nome da classe
    private $variaveis;
    private $post;
    private $postRepository;

    public function __construct(Post $post, PostRepository $postRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'blogPage']]);
        $this->post = $post;
        $this->variaveis = $post->variaveis();
        $this->postRepository = $postRepository;
    }

    public function index()
    {
        $this->authorize('create', new Post());
        $resultados = $this->postRepository->getToTable();
        $tabela = $this->post->tabelaCompleta($resultados);
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

        return redirect(route('posts.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Post criado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($slug)
    {
        $post = $this->postRepository->getBySlug($slug);

        $next = $this->postRepository->getNext($post->id);
        $previous = $this->postRepository->getPrevious($post->id);

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

        return redirect(route('posts.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Post editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy(Post $post)
    {
        $this->authorize('create', $post);

        $post->delete();

        event(new CrudEvent('post', 'apagou', $post->id));

        return redirect(route('posts.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Post deletado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->authorize('create', new Post());

        $busca = IlluminateRequest::input('q');

        $variaveis = (object) $this->variaveis;

        $resultados = $this->postRepository->getBusca($busca);

        $tabela = $this->post->tabelaCompleta($resultados);

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
