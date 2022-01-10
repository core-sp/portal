<?php

namespace App\Http\Controllers;

use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Http\Requests\ConcursoRequest;
use App\Http\Controllers\CrudController;
use App\Repositories\ConcursoRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class ConcursoController extends Controller
{
    use TabelaAdmin;

    private $class = 'ConcursoController';
    private $concursoRepository;
    private $variaveis;
    
    public function __construct(ConcursoRepository $concursoRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid', 'siteBusca']]);
        $this->concursoRepository = $concursoRepository;
        
        $this->variaveis = [
            'singular' => 'concurso',
            'singulariza' => 'o concurso',
            'plural' => 'concursos',
            'pluraliza' => 'concursos',
            'titulo_criar' => 'Cadastrar concurso',
            'btn_criar' => '<a href="' . route('concursos.create') . '" class="btn btn-primary mr-1">Novo Concurso</a>',
            'btn_lixeira' => '<a href="' . route('concursos.lixeira') . '" class="btn btn-warning">Concursos Deletados</a>',
            'btn_lista' => '<a href="' . route('concursos.index') . '" class="btn btn-primary">Lista de Concursos</a>',
            'titulo' => 'Concursos Deletados'
        ];
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        $resultados = $this->concursoRepository->getToTable();
        $tabela = $this->tabelaCompleta($resultados);

        if(auth()->user()->cannot('create', auth()->user())) {
            unset($this->variaveis['btn_criar']);
        }        
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());

        $variaveis = (object) $this->variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function store(ConcursoRequest $request)
    {
        $this->authorize('create', auth()->user());

        $request->validated();

        $save = $this->concursoRepository->store($request);

        if(!$save) {
            abort(500);
        }
            
        event(new CrudEvent('concurso', 'criou', $save->idconcurso));

        return redirect()->route('concursos.index')
            ->with('message', '<i class="icon fa fa-check"></i>Concurso cadastrado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        $resultado = $this->concursoRepository->getById($id);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(ConcursoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        $request->validated();
        
        $update = $this->concursoRepository->update($id, $request); 

        if(!$update) {
            abort(500);
        }
           
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
        $this->authorize('delete', auth()->user());

        $delete = $this->concursoRepository->destroy($id);

        if(!$delete) {
            abort(500);
        }
           
        event(new CrudEvent('concurso', 'apagou', $id));

        return redirect()->route('concursos.index')
            ->with('message', '<i class="icon fa fa-ban"></i>Concurso deletado com sucesso!')
            ->with('class', 'alert-danger');
    }

    public function lixeira()
    {
        $this->authorize('onlyAdmin', auth()->user());

        $resultados = $this->concursoRepository->getTrashed();
        $tabela = $this->tabelaTrashed($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.lixeira', compact('tabela', 'variaveis', 'resultados'));
    }

    public function restore($id)
    {
        $this->authorize('onlyAdmin', auth()->user());

        $restore = $this->concursoRepository->restore($id);

        if(!$restore) {
            abort(500);
        }
            
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
        $tabela = $this->tabelaCompleta($resultados);
        
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteBusca(Request $request)
    {
        $buscaDia = $request->datarealizacao;

        // Se nenhum critério foi fornecido, chama método que abre a tela inical de busca
        if(empty($request->modalidade) && empty($request->situacao) && empty($request->nrprocesso) && empty($request->datarealizacao)) {
            $this->siteGrid();
        }

        if(isset($buscaDia)) {
            $diaArray = explode('/', $buscaDia);
            $checaDia = (count($diaArray) != 3 || $diaArray[2] == null)  ? false : checkdate($diaArray[1], $diaArray[0], $diaArray[2]);

            if($checaDia == false) {
                $concursos = null;

                return view('site.concursos', compact('concursos'))
                    ->with('erro', 'Data fornecida é inválida');
            }

            $buscaDia = date('Y-m-d', strtotime(str_replace('/', '-', $buscaDia)));
        }

        $concursos = $this->concursoRepository->getBuscaSite($request->modalidade, $request->situacao, $request->nrprocesso, $buscaDia);

        $busca = true;

        if (count($concursos) == 0) {
            $concursos = null;
        } 

        return view('site.concursos', compact('concursos', 'busca'));
    }

    public function tabelaCompleta($query)
    {
        $headers = [
            'Código', 
            'Modalidade', 
            'Nº do Processo', 
            'Situação', 
            'Data de Realização', 
            'Ações'
        ];

        $contents = $query->map(function($row) {
            $acoes = '<a href="'.route('concursos.show', $row->idconcurso).'" class="btn btn-sm btn-default">Ver</a> ';
            
            if(auth()->user()->can('updateOther', auth()->user())) {
                $acoes .= '<a href="'.route('concursos.edit', $row->idconcurso).'" class="btn btn-sm btn-primary">Editar</a> ';
            }
                
            if(auth()->user()->can('delete', auth()->user())) {
                $acoes .= '<form method="POST" action="'.route('concursos.destroy', $row->idconcurso).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir o concurso?\')" />';
                $acoes .= '</form>';
            }
            return [
                $row->idconcurso,
                $row->modalidade,
                $row->nrprocesso,
                $row->situacao,
                formataData($row->datarealizacao),
                $acoes
            ];
        })->toArray();

        $classes = [
            'table',
            'table-hover'
        ];

        return $this->montaTabela($headers, $contents, $classes);
    }

    public function tabelaTrashed($query)
    {
        $headers = [
            'Código', 
            'Modalidade', 
            'Nº do Processo', 
            'Deletado em', 
            'Ações'
        ];
        
        $contents = $query->map(function($row){
            $acoes = '<a href="'.route('concursos.restore', $row->idconcurso).'" class="btn btn-sm btn-primary">Restaurar</a>';
            return [
                $row->idconcurso,
                $row->modalidade,
                $row->nrprocesso,
                formataData($row->deleted_at),
                $acoes
            ];
        })->toArray();

        $classes = [
            'table',
            'table-hover'
        ];
        
        return $this->montaTabela($headers, $contents, $classes);
    }
}
