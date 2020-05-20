<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Concurso;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;
use App\Events\CrudEvent;
use App\Http\Requests\ConcursoRequest;
use App\Repositories\ConcursoRepository;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class ConcursoController extends Controller
{
    use ControleAcesso;

    private $class = 'ConcursoController';
    private $concursoModel;
    private $concursoRepository;
    private $variaveis;

    public function __construct(Concurso $concurso, ConcursoRepository $concursoRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid', 'siteBusca']]);
        $this->concursoModel = $concurso;
        $this->concursoRepository = $concursoRepository;
        $this->variaveis = $concurso->variaveis();
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultados = $this->concursoRepository->getToTable();
        $tabela = $this->concursoModel->tabelaCompleta($resultados);
        if(!$this->mostra($this->class, 'create'))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(ConcursoRequest $request)
    {
        $request->validated();

        $save = $this->concursoRepository->store($request);
        if(!$save)
            abort(500);

        event(new CrudEvent('concurso', 'criou', $save->idconcurso));
        return redirect()->route('concursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Concurso cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultado = $this->concursoRepository->getById($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(ConcursoRequest $request, $id)
    {
        $request->validated();
        
        $update = $this->concursoRepository->update($id, $request);        
        if(!$update)
            abort(500);

        event(new CrudEvent('concurso', 'editou', $id));
        return redirect()->route('concursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Concurso editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($id)
    {
        $concurso = $this->concursoRepository->getById($id);
        return response()
            ->view('site.concurso', compact('concurso'))
            ->header('Cache-Control','no-cache');
    }

    public function destroy($id)
    {
        $this->autoriza($this->class, __FUNCTION__);

        $delete = $this->concursoRepository->destroy($id);
        if(!$delete)
            abort(500);

        event(new CrudEvent('concurso', 'apagou', $id));
        return redirect()->route('concursos.index')
            ->with('message', '<i class="icon fa fa-ban"></i>Concurso deletado com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function lixeira()
    {
        $this->autorizaStatic(['1']);
        $resultados = $this->concursoRepository->getTrashed();
        $tabela = $this->concursoModel->tabelaTrashed($resultados);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->autorizaStatic(['1']);

        $restore = $this->concursoRepository->restore($id);
        if(!$restore)
            abort(500);

        event(new CrudEvent('concurso', 'restaurou', $id));
        return redirect()->route('concursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Concurso restaurado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function siteGrid()
    {
        $concursos = $this->concursoRepository->siteGrid();
        return response()
            ->view('site.concursos', compact('concursos'))
            ->header('Cache-Control','no-cache');
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');
        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->concursoRepository->getBusca($busca);
        $tabela = $this->concursoModel->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteBusca()
    {
        $concursos = $this->concursoRepository->getBuscaSite();
        if (!empty(IlluminateRequest::input('modalidade')) 
            or !empty(IlluminateRequest::input('situacao'))
            or !empty(IlluminateRequest::input('nrprocesso'))
            or !empty(IlluminateRequest::input('datarealizacao'))
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        if (count($concursos) > 0) {
            return view('site.concursos', compact('concursos', 'busca'));
        } else {
            $concursos = null;
            return view('site.concursos', compact('concursos', 'busca'));
        }
    }
}
