<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Curso;
use App\CursoInscrito;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\CursoInscritoController;
use App\Events\CrudEvent;
use App\Http\Requests\CursoRequest;
use App\Repositories\CursoRepository;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class CursoController extends Controller
{
    private $class = 'CursoController';
    private $cursoModel;
    private $cursoRepository;
    private $service;
    private $variaveis;

    public function __construct(Curso $curso, CursoRepository $cursoRepository, MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'cursosView']]);
        $this->cursoModel = $curso;
        $this->cursoRepository = $cursoRepository;
        $this->service = $service;
        $this->variaveis = $curso->variaveis();
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());
        $resultados = $this->cursoRepository->getToTable();
        $tabela = $this->cursoModel->tabelaCompleta($resultados);
        if(auth()->user()->cannot('create', auth()->user()))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());
        $variaveis = (object) $this->variaveis;
        $regionais = $this->service->getService('Regional')->getRegionais();
        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }

    public function store(CursoRequest $request)
    {
        $this->authorize('create', auth()->user());

        $request->validated();
        
        $save = $this->cursoRepository->store($request);
        if(!$save)
            abort(500);

        event(new CrudEvent('curso', 'criou', $save->idcurso));
        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Curso criado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        $resultado = Curso::with('regional','user')->findOrFail($id);
        $regionais = $this->service->getService('Regional')->getRegionais();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'regionais', 'variaveis'));
    }

    public function update(CursoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        $request->validated();

        $update = $this->cursoRepository->update($id, $request);
        if(!$update)
            abort(500);

        event(new CrudEvent('curso', 'editou', $id));
        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Curso editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());
        $curso = $this->cursoRepository->getById($id);

        $delete = $curso->delete();
        if(!$delete)
            abort(500);

        event(new CrudEvent('curso', 'cancelou', $curso->idcurso));
        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-ban"></i>Curso cancelado com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function lixeira()
    {
        $this->authorize('onlyAdmin', auth()->user());
        $resultados = $this->cursoRepository->getTrashed();
        $variaveis = (object) $this->variaveis; 
        $tabela = $this->cursoModel->tabelaTrashed($resultados);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->authorize('onlyAdmin', auth()->user());
        $curso = $this->cursoRepository->getTrashedById($id);
        
        $restore = $curso->restore();
        if(!$restore)
            abort(500);
        
        event(new CrudEvent('curso', 'reabriu', $curso->idcurso));
        return redirect()->route('cursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Curso restaurado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function inscritos($id)
    {
        if(perfisPermitidos('CursoInscritoController', 'index'))
        {
            $resultados = CursoInscrito::where('idcurso', $id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $curso = $this->cursoRepository->getById($id);
            $now = date('Y-m-d H:i:s');
            if(!$curso)
                abort(500);
            $variaveis = [
                'pluraliza' => 'inscritos',
                'plural' => 'inscritos',
                'singular' => 'inscrito',
                'singulariza' => 'o inscrito',
                'continuacao_titulo' => 'em <strong>'.$curso->tipo.': '.$curso->tema.'</strong>',
                'btn_lixeira' => '<a href="/admin/cursos" class="btn btn-default">Lista de Cursos</a>',
                'busca' => 'cursos/inscritos/'.$id,
                'addonsHome' => '<a href="/admin/cursos/inscritos/download/'.$id.'" class="btn btn-primary mb-2">Baixar CSV</a>'
            ];
            if($curso->datatermino >= $now) 
                $variaveis['btn_criar'] = '<a href="/admin/cursos/adicionar-inscrito/'.$curso->idcurso.'" class="btn btn-primary mr-1">Adicionar inscrito</a> ';
            if(auth()->user()->cannot('create', auth()->user()))
                unset($variaveis['btn_criar']);
            $tabela = CursoInscritoController::tabelaCompleta($resultados, $curso->idcurso);
            $variaveis = (object) $variaveis;
            return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
        } else
            abort(403);
        
    }

    public function busca()
    {
        $this->authorize('viewAny', auth()->user());
        $busca = IlluminateRequest::input('q');
        $resultados = Curso::where('tipo','LIKE','%'.$busca.'%')
            ->orWhere('tema','LIKE','%'.$busca.'%')
            ->orWhere('descricao','LIKE','%'.$busca.'%')
            ->paginate(10);
        $variaveis = (object) $this->variaveis;
        $tabela = $this->cursoModel->tabelaCompleta($resultados);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function show($id)
    {
        $curso = $this->cursoRepository->getById($id);
        return response()
            ->view('site.curso', compact('curso'))
            ->header('Cache-Control','no-cache');
    }

    public function cursosView()
    {
        $cursos = $this->cursoRepository->getSiteGrid();
        return response()
            ->view('site.cursos', compact('cursos'))
            ->header('Cache-Control','no-cache');
    }
}
