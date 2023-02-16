<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostsController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'blogPage']]);
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

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
        $this->authorize('create', auth()->user());

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
        $this->authorize('create', auth()->user());

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
        try{
            $dados = $this->service->getService('Post')->viewSite($slug);
            $post = $dados['post'];
            $next = $dados['next'];
            $previous = $dados['previous'];
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().' para o slug: '.$slug.'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Post não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().' para o slug: '.$slug.'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página do post no portal.");
        }

        return view('site.post', compact('post', 'next', 'previous'));
    }

    public function blogPage()
    {
        try{
            $posts = $this->service->getService('Post')->siteGrid();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os posts no portal.");
        }

        return view('site.blog', compact('posts'));
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

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
        $this->authorize('updateOther', auth()->user());

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
        $this->authorize('delete', auth()->user());

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
