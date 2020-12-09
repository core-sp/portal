<?php

namespace App\Http\Controllers;

use App\PeriodoFiscalizacao;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\RegionalRepository;
use App\Repositories\FiscalizacaoRepository;
use App\Http\Requests\PeriodoFiscalizacaoRequest;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class FiscalizacaoController extends Controller
{
    use TabelaAdmin, ControleAcesso;

    private $class = 'FiscalizacaoController';
    private $regionalRepository;
    private $fiscalizacaoRepository;
    
    // Variáveis para páginas no Admin
    private $periodoFiscalizacaoVariaveis;
    private $dadosFiscalizacaoVariaveis;

    public function __construct(RegionalRepository $regionalRepository, FiscalizacaoRepository $fiscalizacaoRepository)
    {
        $this->middleware('auth', ['except' => ['mostrarMapa', 'mostrarMapaPeriodo']]);

        $this->regionalRepository = $regionalRepository;
        $this->fiscalizacaoRepository = $fiscalizacaoRepository;

        $this->periodoFiscalizacaoVariaveis = [
            'singular' => 'ano de fiscalização',
            'singulariza' => 'o ano de fiscalização',
            'plural' => 'anos de fiscalização',
            'pluraliza' => 'anos de fiscalização',
            'titulo_criar' => 'Cria ano de fiscalização',
            'busca' => 'fiscalizacao',
            'slug' => 'fiscalizacao'
        ];

        $this->dadosFiscalizacaoVariaveis = [
            'singular' => 'dado de fiscalização',
            'singulariza' => 'o dado de fiscalização',
            'plural' => 'dados de fiscalização',
            'pluraliza' => 'dados de fiscalização',
            'titulo_criar' => 'Registrar dados de fiscalização',
            'form' => 'dadofiscalizacao'
        ];
    }

    public function index()
    {
        $this->autoriza($this->class, "index");

        $resultados = $this->fiscalizacaoRepository->getAll();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->periodoFiscalizacaoVariaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function createPeriodo() 
    {   
        $this->autoriza($this->class, "create");

        $regionais = $this->regionalRepository->getToList();
        $variaveis = $this->periodoFiscalizacaoVariaveis;
        $variaveis['form'] = 'periodofiscalizacaocreate';
        $variaveis = (object) $variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function storePeriodo(PeriodoFiscalizacaoRequest $request)
    {
        $this->autoriza($this->class, "create");

        DB::transaction(function () use ($request) {
            $periodo = $this->fiscalizacaoRepository->storePeriodo($request->toModel());

            $regionais = $this->regionalRepository->getToList();

            foreach($regionais as $regional) {
                $this->fiscalizacaoRepository->storeDadoFiscalizacao($regional->idregional, $periodo->id);
            }
        });

        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>O ano foi criado com sucesso')
            ->with('class', 'alert-success');
    }

    public function updateStatus(Request $request)
    {
        $this->autoriza($this->class, "edit");

        $idusuario = Auth::user()->idusuario;
        $idperiodo = $request->idperiodo;
        $status = $request->status;

        $update = $this->fiscalizacaoRepository->updatePeriodoStatus($idperiodo, ['status' => $status]);

        if(!$update) 
        {
            abort(500);
        }

        if($status) 
        {
            event(new CrudEvent('ano de fiscalização', 'publicou período com ID', $idperiodo));
        } 
        else 
        {
            event(new CrudEvent('ano de fiscalização', 'reverteu publicação do período com ID', $idperiodo));
        }
        
        return redirect()->back()
            ->with('message', '<i class="icon fa fa-check"></i>Status editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function editPeriodo($id)
    {
        $this->autoriza($this->class, "edit");

        $resultado = $this->fiscalizacaoRepository->findOrFail($id);
        $variaveis = $this->periodoFiscalizacaoVariaveis;
        $variaveis['form'] = 'periodofiscalizacaoedit';
        $variaveis = (object) $variaveis;

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }


    public function updatePeriodo(Request $request, $id)
    {
        $this->autoriza($this->class, "edit");

        $dadosFiscalizacao = $request->regional;

        $this->validate($request, [
            'regional.*.*' => 'required|integer|min:0|max:999999999'
        ], [
            'required' => 'Informe o valor',
            'min' => 'Valor deve ser maior ou igual a 0',
            'max' => 'Valor deve ser menor ou igual a 999999999',
            'integer' => 'Valor deve ser um inteiro',
        ]);

        DB::transaction(function () use ($dadosFiscalizacao, $id) {
            $periodo = $this->fiscalizacaoRepository->updateDadoFiscalizacao($dadosFiscalizacao, $id);
        });

        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>O período foi editado com sucesso')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');

        $busca = IlluminateRequest::input('q');
    
        $resultados = $this->fiscalizacaoRepository->busca($busca);

        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->periodoFiscalizacaoVariaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function mostrarMapa()
    {
        $todosPeriodos = $this->fiscalizacaoRepository->getPublicado();
        $periodoSelecionado = $todosPeriodos->first();
        $todosPeriodos = $todosPeriodos->count() == 0 ? null : $todosPeriodos;
        $dataAtualizacao = $periodoSelecionado ? onlyDate($periodoSelecionado->dadoFiscalizacao->sortByDesc("updated_at")->first()->updated_at) : null;

        return view('site.mapa-fiscalizacao', compact('todosPeriodos', 'periodoSelecionado', 'dataAtualizacao'));
    }

    public function mostrarMapaPeriodo($id)
    {
        $todosPeriodos = $this->fiscalizacaoRepository->getPublicado();
        $periodoSelecionado = $todosPeriodos->find($id);

        if(!$periodoSelecionado) {
            return redirect()->route('fiscalizacao.mapa');
        }

        $todosPeriodos = $todosPeriodos->count() == 0 ? null : $todosPeriodos;
        $dataAtualizacao = $periodoSelecionado ? onlyDate($periodoSelecionado->dadoFiscalizacao->sortByDesc("updated_at")->first()->updated_at) : null;

        return view('site.mapa-fiscalizacao', compact('todosPeriodos', 'periodoSelecionado', 'dataAtualizacao'));
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Ano',
            'Status',
            'Ações'
        ];

        // Conteúdo da tabela
        $contents =  $resultados->map(function($row) {
            $acoes = "<form method='POST' id='statusAgendamento' action='" . route('fiscalizacao.updatestatus') . "' class='d-inline'>";
            $acoes .= "<input type='hidden' name='idperiodo' value='$row->id'/>";
            $acoes .= "<input type='hidden' name='_token' value='" . csrf_token() . "'/>";
            
            if($row->status) {
                if($this->mostra($this->class, 'edit')) {
                    $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-danger ml-1' value='0'>Reverter Publicação</button>";
                }
                 
                $status = PeriodoFiscalizacao::STATUS_PUBLICADO;
            }
            else {
                if($this->mostra($this->class, 'edit')) {
                    $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-primary' value='1'>Publicar</button>";
                }
                
                $status = PeriodoFiscalizacao::STATUS_NAO_PUBLICADO;
            }

            $acoes .= "</form>";

            if($this->mostra($this->class, 'edit')) {
                $acoes .= " <a href='" . route('fiscalizacao.editperiodo', $row->id) . "' class='btn btn-sm btn-default'>Editar</a>";
            }
            
            return [
                $row->periodo,
                $status,
                $acoes
            ];
        })->toArray();

        // Classes da tabela
        $classes = [
            'table',
            'table-bordered',
            'table-striped'
        ];
        
        return $this->montaTabela($headers, $contents, $classes);
    }
}
