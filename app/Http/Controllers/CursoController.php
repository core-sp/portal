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
use App\Repositories\RegionalRepository;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class CursoController extends Controller
{
    use ControleAcesso;

    private $class = 'CursoController';
    private $cursoModel;
    private $cursoRepository;
    private $variaveis;

    public function __construct(Curso $curso, CursoRepository $cursoRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'cursosView']]);
        $this->cursoModel = $curso;
        $this->cursoRepository = $cursoRepository;
        $this->variaveis = $curso->variaveis();
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $resultados = $this->cursoRepository->getToTable();
        $tabela = $this->cursoModel->tabelaCompleta($resultados);
        if(!$this->mostra($this->class, 'create'))
            unset($this->variaveis['btn_criar']);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->autoriza($this->class, __FUNCTION__);
        $variaveis = (object) $this->variaveis;
        $regionais = (new RegionalRepository)->all();
        return view('admin.crud.criar', compact('variaveis', 'regionais'));
    }

    public function store(CursoRequest $request)
    {
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
        $this->autoriza($this->class, __FUNCTION__);
        $resultado = Curso::with('regional','user')->findOrFail($id);
        $regionais = (new RegionalRepository)->all();
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'regionais', 'variaveis'));
    }

    public function update(CursoRequest $request, $id)
    {
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
        $this->autoriza($this->class, __FUNCTION__);
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
        $this->autorizaStatic(['1']);
        $resultados = $this->cursoRepository->getTrashed();
        $variaveis = (object) $this->variaveis; 
        $tabela = $this->cursoModel->tabelaTrashed($resultados);
        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->autorizaStatic(['1']);
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
        $this->autoriza('CursoInscritoController', 'index');
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
        if(!$this->mostra('CursoInscritoController', 'create'))
            unset($variaveis['btn_criar']);
        $tabela = CursoInscritoController::tabelaCompleta($resultados, $curso->idcurso);
        $variaveis = (object) $variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');
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
