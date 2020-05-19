<?php

namespace App\Http\Controllers;

use App\Noticia;
use App\Regional;
use Illuminate\Support\Str;
use App\Events\CrudEvent;
use App\Http\Requests\NoticiaRequest;
use App\Repositories\NoticiaRepository;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class NoticiaController extends Controller
{
    use ControleAcesso;

    // Nome da classe
    private $class = 'NoticiaController';
    private $noticiaModel;
    private $noticiaRepository;
    private $variaveis;

    public function __construct(Noticia $noticia, NoticiaRepository $noticiaRepository)
    {
        $this->middleware('auth', ['except' => 'show', 'siteGrid']);
        $this->noticiaModel = $noticia;
        $this->noticiaRepository = $noticiaRepository;
        $this->variaveis = $noticia->variaveis();
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultados = $this->noticiaRepository->getToTable();
        $tabela = $this->noticiaModel->tabelaCompleta($this->noticiaRepository->getToTable());
        if(!$this->mostra($this->class, 'create'))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $regionais = Regional::orderBy('regional', 'ASC')->get();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }

    public function store(NoticiaRequest $request)
    {
        $request->validated();
        // Conta se título de notícia já existe
        $slug = Str::slug($request->input('titulo'), '-');
        $countTitulo = $this->noticiaRepository->getExistingSlug($slug);
        if($countTitulo >= 1) {
            return redirect(route('noticias.index'))
                ->with('message', '<i class="icon fa fa-ban"></i>Não foi possível criar a notícia, pois já existe uma notícia com o título utilizado.')
                ->with('class', 'alert-danger');
        }
        // Inputa dados no BD
        $save = $this->noticiaRepository->store($request, $slug);
        // Aborta se algo deu errado
        if(!$save)
            abort(500);
        // Gera evento no log e redireciona
        event(new CrudEvent('notícia', 'criou', $save->idnoticia));
        return redirect(route('noticias.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Notícia criada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultado = Noticia::findOrFail($id);
        $regionais = Regional::orderBy('regional', 'ASC')->get();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regionais'));
    }

    public function update(NoticiaRequest $request, $id)
    {
        $request->validated();
        // Checa se slug já existe
        $slug = Str::slug($request->input('titulo'), '-');
        $countTitulo = $this->noticiaRepository->getExistingSlug($slug, $id);
        if($countTitulo >= 1) {
            return redirect(route('noticias.index'))
                ->with('message', '<i class="icon fa fa-ban"></i>Não foi possível editar a notícia, pois já existe uma notícia com o título utilizado.')
                ->with('class', 'alert-danger');
        }
        // Inputa dados no BD
        $update = $this->noticiaRepository->update($id, $request, $slug);
        // Aborta se algo dá errado
        if(!$update)
            abort(500);
        // Gera evento e redireciona
        event(new CrudEvent('notícia', 'editou', $id));
        return redirect(route('noticias.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Notícia editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($slug)
    {
        $noticia = $this->noticiaRepository->getBySlug($slug);
        $titulo = $noticia->titulo;
        $id = $noticia->idnoticia;
        $tres = $this->noticiaRepository->getThreeExcludingOneById($id);
        return response()
            ->view('site.noticia', compact('noticia', 'titulo', 'tres', 'id'))
            ->header('Cache-Control','no-cache');
    }

    public function destroy($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $delete = $this->noticiaRepository->destroy($id);
        if(!$delete)
            abort(500);
        event(new CrudEvent('notícia', 'apagou', $id));
        return redirect('/admin/noticias')
            ->with('message', '<i class="icon fa fa-ban"></i>Notícia deletada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function lixeira()
    {
        $this->autorizaStatic(['1']);
        $resultados = $this->noticiaRepository->getTrashed();
        $variaveis = (object) $this->variaveis;
        $tabela = $this->noticiaModel->tabelaTrashed($resultados);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->autorizaStatic(['1']);
        $restore = $this->noticiaRepository->getTrashedById($id)->restore();
        if(!$restore)
            abort(500);
        event(new CrudEvent('notícia', 'restaurou', $id));
        return redirect('/admin/noticias')
            ->with('message', '<i class="icon fa fa-check"></i>Notícia restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->noticiaRepository->getBusca($busca);
        $tabela = $this->noticiaModel->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteGrid()
    {
        $noticias = $this->noticiaRepository->getSiteGrid();
        return view('site.noticias', compact('noticias'));
    }
}
