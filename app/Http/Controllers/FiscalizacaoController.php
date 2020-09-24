<?php

namespace App\Http\Controllers;

use App\AnoFiscalizacao;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\RegionalRepository;
use App\Repositories\FiscalizacaoRepository;
use App\Http\Requests\AnoFiscalizacaoRequest;

class FiscalizacaoController extends Controller
{
    use TabelaAdmin;

    private $regionalRepository;
    private $fiscalizacaoRepository;
    
    // Variáveis para páginas no Admin
    private $anoFiscalizacaoVariaveis = [
        'singular' => 'ano de fiscalização',
        'singulariza' => 'o ano de fiscalização',
        'plural' => 'anos de fiscalização',
        'pluraliza' => 'anos de fiscalização',
        'titulo_criar' => 'Abrir ano de fiscalização'
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
        $resultados = $this->fiscalizacaoRepository->getToTable();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->anoFiscalizacaoVariaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function createAno() 
    {
        $regionais = $this->regionalRepository->getToList();
        $variaveis = $this->anoFiscalizacaoVariaveis;
        $variaveis['form'] = 'anofiscalizacaocreate';
        $variaveis = (object) $variaveis;

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function storeAno(AnoFiscalizacaoRequest $request)
    {
        DB::transaction(function () use ($request) {
            $ano = $this->fiscalizacaoRepository->storeAno($request->toModel());

            $regionais = $this->regionalRepository->getToList();

            foreach($regionais as $regional) {
                $this->fiscalizacaoRepository->storeDadoFiscalizacao($regional->idregional, $ano->ano);
            }
        });

        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>O ano foi aberto com sucesso')
            ->with('class', 'alert-success');
    }

    public function updateStatus(Request $request)
    {
        $idusuario = Auth::user()->idusuario;
        $ano = $request->ano;
        $status = $request->status;

        $update = $this->fiscalizacaoRepository->updateAno($ano, ['status' => $status]);

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
        $resultado = $this->fiscalizacaoRepository->findOrFail($ano);
        $variaveis = $this->anoFiscalizacaoVariaveis;
        $variaveis['form'] = 'anofiscalizacaoedit';
        $variaveis = (object) $variaveis;

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }


    public function updateAno(Request $request)
    {
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function mostrarMapa()
    {
        $todosAnos = $this->fiscalizacaoRepository->getAll();
        $anoSelecionado = $todosAnos->first();
        $anos = [];
        
        foreach($todosAnos as $ano) {
            array_push($anos, $ano->ano);
        }

        return view('site.mapa-fiscalizacao', compact('anos', 'anoSelecionado'));
    }

    public function mostrarMapaAno($ano)
    {
        $todosAnos = $this->fiscalizacaoRepository->getAll();
        $anoSelecionado = $todosAnos->find($ano);

        if(!$anoSelecionado) {
            return redirect()->route('fiscalizacao.mapa');
        }

        $anos = [];
        
        foreach($todosAnos as $ano) {
            array_push($anos, $ano->ano);
        }

        return view('site.mapa-fiscalizacao', compact('anos', 'anoSelecionado'));
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
                $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-danger ml-1' value='0'>Reverter Publicação</button>";
                $status = AnoFiscalizacao::$status_publicado;
            }
            else {
                $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-primary' value='1'>Publicar</button>";
                $status = AnoFiscalizacao::$status_nao_publicado;
            }

            $acoes .= "</form>";

            $acoes .= " <a href='" . route('fiscalizacao.editano', $row->ano) . "' class='btn btn-sm btn-default'>Editar</a>";
            
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
