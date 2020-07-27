<?php

namespace App\Http\Controllers;

use App\Events\CrudEvent;
use App\Post;
use App\Pagina;
use App\Http\Requests\PostRequest;
use App\Repositories\PostRepository;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PostsController extends Controller
{
    use ControleAcesso;

    private $class = 'PostsController';
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
        // Verificando permissão de visualização de posts
        $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->postRepository->getToTable();
        $tabela = $this->post->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        // Verificando permissão de criação de posts
        $this->autoriza($this->class, __FUNCTION__);

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(PostRequest $request)
    {
        // Verificando permissão de criação de posts (mesma do create)
        $this->autoriza($this->class, 'create');

        $request->validated();

        $save = $this->postRepository->store($request->toModel());

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
        // Verificando permissão de edição de posts
        $this->autoriza($this->class, __FUNCTION__);

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.editar', compact('post', 'variaveis'));
    }

    public function update(PostRequest $request, $id)
    {
        // Verificando permissão de edição de posts (mesma do edit)
        $this->autoriza($this->class, 'edit');
        
        $request->validated();

        $save = $this->postRepository->update($id, $request->toModel());

        event(new CrudEvent('post', 'editou', $id));

        return redirect(route('posts.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Post editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        // Verificando permissão de remoção de posts
        $this->autoriza($this->class, __FUNCTION__);

        $this->postRepository->delete($id);

        event(new CrudEvent('post', 'apagou', $id));

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
