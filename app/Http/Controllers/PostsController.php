<?php

namespace App\Http\Controllers;

// use App\Events\CrudEvent;
// use App\Post;
// use App\Pagina;
use App\Http\Requests\PostRequest;
// use App\Repositories\PostRepository;
// use Illuminate\Support\Facades\Request as IlluminateRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    // private $class = 'PostsController';
    // private $variaveis;
    // private $post;
    // private $postRepository;
    private $service;

    public function __construct(MediadorServiceInterface $service/*, Post $post, PostRepository $postRepository*/)
    {
        $this->middleware('auth', ['except' => ['show', 'blogPage']]);
        // $this->post = $post;
        // $this->variaveis = $post->variaveis();
        // $this->postRepository = $postRepository;
        $this->service = $service;
    }

    public function index()
    {
        // Verificando permissão de visualização de posts
        $this->authorize('viewAny', auth()->user());

        // $resultados = $this->postRepository->getToTable();
        // $tabela = $this->post->tabelaCompleta($resultados);
        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Post')->listar();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os posts.");
        }
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        // Verificando permissão de criação de posts
        $this->authorize('create', auth()->user());

        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Post')->view();
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para criar o post.");
        }

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(PostRequest $request)
    {
        // Verificando permissão de criação de posts (mesma do create)
        $this->authorize('create', auth()->user());

        // $request->validated();

        // $save = $this->postRepository->store($request->toModel());

        // event(new CrudEvent('post', 'criou', $save->id));

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $this->service->getService('Post')->save($validated, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar o post.");
        }

        return redirect(route('posts.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Post criado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($slug)
    {
        // $post = $this->postRepository->getBySlug($slug);

        // $next = $this->postRepository->getNext($post->id);
        // $previous = $this->postRepository->getPrevious($post->id);

        try{
            $dados = $this->service->getService('Post')->viewSite($slug);
            $post = $dados['post'];
            $next = $dados['next'];
            $previous = $dados['previous'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página do post no portal.");
        }

        return view('site.post', compact('post', 'next', 'previous'));
    }

    public function blogPage()
    {
        // $posts = Post::orderBy('created_at', 'DESC')->paginate(9);

        try{
            $posts = $this->service->getService('Post')->siteGrid();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os posts no portal.");
        }

        return view('site.blog', compact('posts'));
    }

    public function edit($id/*Post $post*/)
    {
        // Verificando permissão de edição de posts
        $this->authorize('updateOther', auth()->user());

        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Post')->view($id);
            $variaveis = $dados['variaveis'];
            $post = $dados['post'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para editar o post.");
        }

        return view('admin.crud.editar', compact('post', 'variaveis'));
    }

    public function update(PostRequest $request, $id)
    {
        // Verificando permissão de edição de posts (mesma do edit)
        $this->authorize('updateOther', auth()->user());
        
        // $request->validated();

        // $save = $this->postRepository->update($id, $request->toModel());

        // event(new CrudEvent('post', 'editou', $id));

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $this->service->getService('Post')->save($validated, $user, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao editar o post.");
        }

        return redirect(route('posts.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Post com a ID: ' . $id . ' foi editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        // Verificando permissão de remoção de posts
        $this->authorize('delete', auth()->user());

        // $this->postRepository->delete($id);

        // event(new CrudEvent('post', 'apagou', $id));

        try{
            $this->service->getService('Post')->destroy($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao excluir o post.");
        }

        return redirect(route('posts.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Post com a ID: ' . $id . ' foi deletado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        // $busca = IlluminateRequest::input('q');

        // $variaveis = (object) $this->variaveis;

        // $resultados = $this->postRepository->getBusca($busca);

        // $tabela = $this->post->tabelaCompleta($resultados);

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Post')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em posts.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
