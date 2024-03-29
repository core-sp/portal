<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pagina;
use Illuminate\Support\Str;
use App\Events\CrudEvent;
use App\Http\Requests\PaginaRequest;
use App\Repositories\PaginaRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PaginaController extends Controller
{
    // Nome da classe
    private $class = 'PaginaController';
    private $paginaModel;
    private $variaveis;
    private $paginaRepository;

    public function __construct(Pagina $pagina, PaginaRepository $paginaRepository)
    {
        $this->middleware('auth', ['except' => ['show']]);
        $this->paginaModel = $pagina;
        $this->variaveis = $pagina->variaveis();
        $this->paginaRepository = $paginaRepository;
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());
        $resultados = $this->paginaRepository->getToTable();
        $tabela = $this->paginaModel->tabelaCompleta($resultados);
        if(auth()->user()->cannot('create', auth()->user()))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(PaginaRequest $request)
    {
        $this->authorize('create', auth()->user());

        $request->validated();

        $slug = Str::slug($request->input('titulo'), '-');
        $countTitulo = $this->paginaRepository->countBySlug($slug);
        if($countTitulo >= 1) {
            return redirect(route('paginas.index'))
                ->with('message', '<i class="icon fa fa-ban"></i>Não foi possível criar a página. Já existe uma página com esse nome.')
                ->with('class', 'alert-danger');
        }

        $save = $this->paginaRepository->store($request, $slug);
        if(!$save)
            abort(500);

        event(new CrudEvent('página', 'criou', $save->idpagina));
        return redirect(route('paginas.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Página criada com sucesso!')
            ->with('class', 'alert-success');
    }    

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        $resultado = $this->paginaRepository->findById($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(PaginaRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        $request->validated();
        
        $slug = Str::slug($request->input('titulo'), '-');
        $countTitulo = $this->paginaRepository->countBySlug($slug, $id);
        if($countTitulo >= 1) {
            return redirect(route('paginas.index'))
                ->with('message', '<i class="icon fa fa-ban"></i>Não foi possível criar a página. Já existe uma página com esse nome.')
                ->with('class', 'alert-danger');
        }

        $update = $this->paginaRepository->update($id, $request, $slug);
        if(!$update)
            abort(500);

        event(new CrudEvent('página', 'editou', $id));
        return redirect(route('paginas.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Página editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($slug)
    {
        $pagina = $this->paginaRepository->show($slug);
        return isset($pagina) ? response()->view('site.pagina', compact('pagina'))->header('Cache-Control','no-cache') : abort(404);
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());
        
        $delete = $this->paginaRepository->findById($id)->delete();
        if(!$delete)
            abort(500);
        
        event(new CrudEvent('página', 'apagou', $id));
        return redirect(route('paginas.index'))
            ->with('message', '<i class="icon fa fa-ban"></i>Página deletada com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function lixeira()
    {
        $this->authorize('onlyAdmin', auth()->user());
        $resultados = $this->paginaRepository->getTrashed();
        $variaveis = (object) $this->variaveis;
        $tabela = $this->paginaModel->tabelaTrashed($resultados);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $restore = $this->paginaRepository->getTrashedById($id)->restore();
        if(!$restore)
            abort(500);
        
        event(new CrudEvent('página', 'restaurou', $id));
        return redirect(route('paginas.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Página restaurada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->authorize('viewAny', auth()->user());
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->paginaRepository->getBusca($busca);
        $tabela = $this->paginaModel->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'variaveis', 'tabela', 'busca'));
    }

}
