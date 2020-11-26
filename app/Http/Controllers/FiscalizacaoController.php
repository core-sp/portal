<?php

namespace App\Http\Controllers;

use App\AnoFiscalizacao;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Traits\ControleAcesso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\RegionalRepository;
use App\Repositories\FiscalizacaoRepository;
use App\Http\Requests\AnoFiscalizacaoRequest;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class FiscalizacaoController extends Controller
{
    use TabelaAdmin, ControleAcesso;

    private $class = 'FiscalizacaoController';
    private $regionalRepository;
    private $fiscalizacaoRepository;
    
    // Variáveis para páginas no Admin
    private $anoFiscalizacaoVariaveis = [
        'singular' => 'ano de fiscalização',
        'singulariza' => 'o ano de fiscalização',
        'plural' => 'anos de fiscalização',
        'pluraliza' => 'anos de fiscalização',
        'titulo_criar' => 'Cria ano de fiscalização',
        'busca' => 'fiscalizacao',
        'slug' => 'fiscalizacao'
    ];
    private $dadosFiscalizacaoVariaveis = [
        'singular' => 'dado de fiscalização',
        'singulariza' => 'o dado de fiscalização',
        'plural' => 'dados de fiscalização',
        'pluraliza' => 'dados de fiscalização',
        'titulo_criar' => 'Registrar dados de fiscalização',
        'form' => 'dadofiscalizacao'
    ];

    public function __construct(RegionalRepository $regionalRepository, FiscalizacaoRepository $fiscalizacaoRepository)
    {
        $this->middleware('auth', ['except' => ['mostrarMapa', 'mostrarMapaAno']]);

        $this->regionalRepository = $regionalRepository;
        $this->fiscalizacaoRepository = $fiscalizacaoRepository;
    }

    public function index()
    {
        $this->autoriza($this->class, "index");

        $resultados = $this->fiscalizacaoRepository->getAll();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->anoFiscalizacaoVariaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function createAno() 
    {   
        $this->autoriza($this->class, "create");

        $regionais = $this->regionalRepository->getToList();
        $variaveis = $this->anoFiscalizacaoVariaveis;
        $variaveis['form'] = 'anofiscalizacaocreate';
        $variaveis = (object) $variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function storeAno(AnoFiscalizacaoRequest $request)
    {
        $this->autoriza($this->class, "create");

        DB::transaction(function () use ($request) {
            $ano = $this->fiscalizacaoRepository->storeAno($request->toModel());

            $regionais = $this->regionalRepository->getToList();

            foreach($regionais as $regional) {
                $this->fiscalizacaoRepository->storeDadoFiscalizacao($regional->idregional, $ano->ano);
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
        $ano = $request->ano;
        $status = $request->status;

        $update = $this->fiscalizacaoRepository->updateAnoStatus($ano, ['status' => $status]);

        if(!$update) 
        {
            abort(500);
        }

        if($status) 
        {
            event(new CrudEvent('ano de fiscalização', 'publicou', $ano));
        } 
        else 
        {
            event(new CrudEvent('ano de fiscalização', 'reverteu publicação', $ano));
        }
        
        return redirect()->back()
            ->with('message', '<i class="icon fa fa-check"></i>Status editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function editAno($ano)
    {
        $this->autoriza($this->class, "edit");

        $resultado = $this->fiscalizacaoRepository->findOrFail($ano);
        $variaveis = $this->anoFiscalizacaoVariaveis;
        $variaveis['form'] = 'anofiscalizacaoedit';
        $variaveis = (object) $variaveis;

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }


    public function updateAno(Request $request, $ano)
    {
        $this->autoriza($this->class, "edit");

        $dadosFiscalizacao = $request->regional;

        DB::transaction(function () use ($dadosFiscalizacao, $ano) {
            $ano = $this->fiscalizacaoRepository->updateDadoFiscalizacao($dadosFiscalizacao, $ano);
        });

        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>O ano foi editado com sucesso')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');

        $busca = IlluminateRequest::input('q');
    
        $resultados = $this->fiscalizacaoRepository->busca($busca);

        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->anoFiscalizacaoVariaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function mostrarMapa()
    {
        $todosAnos = $this->fiscalizacaoRepository->getPublicado();
        $anoSelecionado = $todosAnos->first();
        $dataAtualizacao = $anoSelecionado ? onlyDate($anoSelecionado->dadoFiscalizacao->sortByDesc("updated_at")->first()->updated_at) : null;
        $anos = [];
        
        foreach($todosAnos as $ano) {
            array_push($anos, $ano->ano);
        }

        return view('site.mapa-fiscalizacao', compact('anos', 'anoSelecionado', 'dataAtualizacao'));
    }

    public function mostrarMapaAno($ano)
    {
        $todosAnos = $this->fiscalizacaoRepository->getPublicado();
        $anoSelecionado = $todosAnos->find($ano);

        if(!$anoSelecionado) {
            return redirect()->route('fiscalizacao.mapa');
        }

        $dataAtualizacao = $anoSelecionado ? onlyDate($anoSelecionado->dadoFiscalizacao->sortByDesc("updated_at")->first()->updated_at) : null;
        $anos = [];
        
        foreach($todosAnos as $ano) {
            array_push($anos, $ano->ano);
        }

        return view('site.mapa-fiscalizacao', compact('anos', 'anoSelecionado', 'dataAtualizacao'));
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
            $acoes .= "<input type='hidden' name='ano' value='$row->ano'/>";
            $acoes .= "<input type='hidden' name='_token' value='" . csrf_token() . "'/>";
            
            if($row->status) {
                if($this->mostra($this->class, 'edit')) {
                    $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-danger ml-1' value='0'>Reverter Publicação</button>";
                }
                 
                $status = AnoFiscalizacao::STATUS_PUBLICADO;
            }
            else {
                if($this->mostra($this->class, 'edit')) {
                    $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-primary' value='1'>Publicar</button>";
                }
                
                $status = AnoFiscalizacao::STATUS_NAO_PUBLICADO;
            }

            $acoes .= "</form>";

            if($this->mostra($this->class, 'edit')) {
                $acoes .= " <a href='" . route('fiscalizacao.editano', $row->ano) . "' class='btn btn-sm btn-default'>Editar</a>";
            }
            
            return [
                $row->ano,
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
