<?php

namespace App\Http\Controllers;

// use App\Noticia;
// use Illuminate\Support\Str;
// use App\Events\CrudEvent;
use App\Http\Requests\NoticiaRequest;
// use App\Repositories\NoticiaRepository;
use App\Contracts\MediadorServiceInterface;
// use Illuminate\Support\Facades\Request as IlluminateRequest;
use Illuminate\Http\Request;

class NoticiaController extends Controller
{
    // private $class = 'NoticiaController';
    // private $noticiaModel;
    // private $noticiaRepository;
    private $service;
    // private $variaveis;

    public function __construct(/*Noticia $noticia, NoticiaRepository $noticiaRepository, */MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid']]);
        // $this->noticiaModel = $noticia;
        // $this->noticiaRepository = $noticiaRepository;
        $this->service = $service;
        // $this->variaveis = $noticia->variaveis();
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        // $resultados = $this->noticiaRepository->getToTable();
        // $tabela = $this->noticiaModel->tabelaCompleta($this->noticiaRepository->getToTable());
        // if(auth()->user()->cannot('create', auth()->user()))
        //     unset($this->variaveis['btn_criar']);
        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Noticia')->listar();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as notícias.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());

        // $regionais = $this->service->getService('Regional')->all()->sortBy('regional');
        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Noticia')->view($this->service);
            $variaveis = $dados['variaveis'];
            $regionais = $dados['regionais'];
            $categorias = $dados['categorias'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para criar a notícia.");
        }

        return view('admin.crud.criar', compact('variaveis', 'regionais', 'categorias'));
    }

    public function store(NoticiaRequest $request)
    {
        $this->authorize('create', auth()->user());

        // $request->validated();
        // // Conta se título de notícia já existe
        // $slug = Str::slug($request->input('titulo'), '-');
        // $countTitulo = $this->noticiaRepository->getExistingSlug($slug);
        // if($countTitulo >= 1) {
        //     return redirect(route('noticias.index'))
        //         ->with('message', '<i class="icon fa fa-ban"></i>Não foi possível criar a notícia, pois já existe uma notícia com o título utilizado.')
        //         ->with('class', 'alert-danger');
        // }
        // // Inputa dados no BD
        // $save = $this->noticiaRepository->store($request, $slug);
        // // Aborta se algo deu errado
        // if(!$save)
        //     abort(500);
        // // Gera evento no log e redireciona
        // event(new CrudEvent('notícia', 'criou', $save->idnoticia));

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $this->service->getService('Noticia')->save($validated, $user);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar a notícia.");
        }

        return redirect(route('noticias.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Notícia criada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        // $resultado = Noticia::findOrFail($id);
        // $regionais = $this->service->getService('Regional')->all()->sortBy('regional');
        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Noticia')->view($this->service, $id);
            $resultado = $dados['resultado'];
            $variaveis = $dados['variaveis'];
            $regionais = $dados['regionais'];
            $categorias = $dados['categorias'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página para editar a notícia.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regionais', 'categorias'));
    }

    public function update(NoticiaRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        // $request->validated();
        // // Checa se slug já existe
        // $slug = Str::slug($request->input('titulo'), '-');
        // $countTitulo = $this->noticiaRepository->getExistingSlug($slug, $id);
        // if($countTitulo >= 1) {
        //     return redirect(route('noticias.index'))
        //         ->with('message', '<i class="icon fa fa-ban"></i>Não foi possível editar a notícia, pois já existe uma notícia com o título utilizado.')
        //         ->with('class', 'alert-danger');
        // }
        // // Inputa dados no BD
        // $update = $this->noticiaRepository->update($id, $request, $slug);
        // // Aborta se algo dá errado
        // if(!$update)
        //     abort(500);
        // // Gera evento e redireciona
        // event(new CrudEvent('notícia', 'editou', $id));

        try{
            $validated = $request->validated();
            $user = auth()->user();
            $this->service->getService('Noticia')->save($validated, $user, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao editar a notícia.");
        }

        return redirect(route('noticias.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Notícia com a ID: ' . $id . ' foi editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($slug)
    {
        // $noticia = $this->noticiaRepository->getBySlug($slug);
        // $titulo = $noticia->titulo;
        // $id = $noticia->idnoticia;
        // $tres = $this->noticiaRepository->getThreeExcludingOneById($id);

        try{
            $dados = $this->service->getService('Noticia')->viewSite($slug);
            $noticia = $dados['noticia'];
            $tres = $dados['tres'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a página da notícia no portal.");
        }

        return response()
            ->view('site.noticia', compact('noticia', 'tres'))
            ->header('Cache-Control','no-cache');
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());

        // $delete = $this->noticiaRepository->destroy($id);
        // if(!$delete)
        //     abort(500);
        // event(new CrudEvent('notícia', 'apagou', $id));

        try{
            $this->service->getService('Noticia')->destroy($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao excluir a notícia.");
        }

        return redirect(route('noticias.index'))
            ->with('message', '<i class="icon fa fa-ban"></i>Notícia com a ID: ' . $id . ' foi deletada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function lixeira()
    {
        $this->authorize('onlyAdmin', auth()->user());

        // $resultados = $this->noticiaRepository->getTrashed();
        // $variaveis = (object) $this->variaveis;
        // $tabela = $this->noticiaModel->tabelaTrashed($resultados);

        try{
            $dados = $this->service->getService('Noticia')->lixeira();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as notícias excluídas.");
        }

        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        // $restore = $this->noticiaRepository->getTrashedById($id)->restore();
        // if(!$restore)
        //     abort(500);
        // event(new CrudEvent('notícia', 'restaurou', $id));

        try{
            $this->service->getService('Noticia')->restore($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao restaurar a notícia.");
        }

        return redirect(route('noticias.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Notícia com a ID: ' . $id . ' foi restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        // $busca = IlluminateRequest::input('q');
        // $variaveis = (object) $this->variaveis;
        // $resultados = $this->noticiaRepository->getBusca($busca);
        // $tabela = $this->noticiaModel->tabelaCompleta($resultados);

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Noticia')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em notícias.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteGrid()
    {
        // $noticias = $this->noticiaRepository->getSiteGrid();

        try{
            $noticias = $this->service->getService('Noticia')->siteGrid();
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as notícias no portal.");
        }

        return view('site.noticias', compact('noticias'));
    }
}
