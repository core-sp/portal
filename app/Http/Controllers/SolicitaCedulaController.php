<?php

namespace App\Http\Controllers;

use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\SolicitaCedula;
use App\Traits\ControleAcesso;
use App\Http\Controllers\ControleController;
use App\Repositories\GerentiRepositoryInterface;
use App\Repositories\SolicitaCedulaRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class SolicitaCedulaController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    private $class = 'SolicitaCedulaController';
    private $gerentiRepository;
    private $solicitaCedulaRepository;

    // Variáveis
    public $variaveis = [
        'singular' => 'solicitação de cédula',
        'singulariza' => 'a solicitação de cédula',
        'plural' => 'solicitações de cédulas',
        'pluraliza' => 'solicitações de cédulas',
        'mostra' => 'solicita-cedula',
        'busca' => 'solicita-cedulas'
    ];

    public function __construct(SolicitaCedulaRepository $solicitaCedulaRepository)
    {
        // $this->middleware('auth');
        $this->solicitaCedulaRepository = $solicitaCedulaRepository;
    }

    public function resultados()
    {
        $resultados = $this->solicitaCedulaRepository->getAll();

        return $resultados;
    }

    public function show($id)
    {
        // $this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->solicitaCedulaRepository->getById($id);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.mostra', compact('resultado', 'variaveis'));
    }

    public function inserirSolicitacaoCedula(Request $request)
    {
        // $this->gerentiRepository->gerentiInserirEndereco($request->ass_id, unserialize($request->infos));

        $this->solicitaCedulaRepository->updateStatusEmAndamento($request->id);

        event(new CrudEvent('endereço representante', 'enviou para o Gerenti', $request->id));

        return redirect('/admin/representante-enderecos')
                ->with('message', 'O endereço foi cadastrado com sucesso no Gerenti.')
                ->with('class', 'alert-success');
    }

    public function reprovarCedula(Request $request)
    {
        $this->representanteEnderecoRepository->updateStatusRecusado($request->id, $request->observacao);

        event(new CrudEvent('endereço representante', 'recusou', $request->id));

        return redirect('/admin/representante-enderecos')
                ->with('message', 'A atualização de endereço foi recusada.')
                ->with('class', 'alert-info');
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'ID do Representante',
            'Solicitado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/solicita-cedula/' . $resultado->id . '" class="btn btn-sm btn-default">Ver</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->idrepresentante,
                formataData($resultado->created_at),
                $this->showStatus($resultado->status),
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        // Monta e retorna tabela        
        $tabela = $this->montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    protected function showStatus($string)
    {
        switch ($string) {
            case SolicitaCedula::STATUS_EM_ANDAMENTO:
                return '<strong><i>Em andamento</i></strong>';
            break;

            case SolicitaCedula::STATUS_REPROVADO:
                return '<strong class="text-danger">Reprovado</strong>';
            break;

            case SolicitaCedula::STATUS_APROVADO:
                return '<strong class="text-success">Aprovado</strong>';
            break;
            
            default:
                return $string;
            break;
        }
    }

    public function index()
    {
        // $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        // $this->autoriza($this->class, 'index');

        $busca = IlluminateRequest::input('q');

        $resultados = $this->solicitaCedulaRepository->getBusca($busca);
        
        $tabela = $this->tabelaCompleta($resultados);
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
