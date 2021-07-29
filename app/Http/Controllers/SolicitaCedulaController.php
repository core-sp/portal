<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\SolicitaCedula;
use App\Traits\ControleAcesso;
use App\Http\Controllers\ControleController;
use App\Repositories\SolicitaCedulaRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class SolicitaCedulaController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    private $class = 'SolicitaCedulaController';
    private $solicitaCedulaRepository;

    // Variáveis
    public $variaveis = [
        'singular' => 'solicitação de cédula',
        'singulariza' => 'a solicitação de cédula',
        'plural' => 'solicitações de cédulas',
        'pluraliza' => 'solicitações de cédulas',
        'mostra' => 'solicita-cedula',
        'slug' => 'solicita-cedula',
        'busca' => 'solicita-cedulas'
    ];

    public function __construct(SolicitaCedulaRepository $solicitaCedulaRepository)
    {
        $this->middleware('auth');
        $this->solicitaCedulaRepository = $solicitaCedulaRepository;
    }

    public function resultados()
    {
        $resultados = $this->solicitaCedulaRepository->getAll();

        return $resultados;
    }

    public function show($id)
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->solicitaCedulaRepository->getById($id);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.mostra', compact('resultado', 'variaveis'));
    }

    public function inserirSolicitaCedula(Request $request)
    {
        $this->solicitaCedulaRepository->updateStatusAceito($request->id, Auth::user()->idusuario);

        event(new CrudEvent('solicitação de cédula alterada', 'atendente aceitou', $request->id));

        return redirect('/admin/solicita-cedula')
                ->with('message', 'A solicitação de cédula foi cadastrada com sucesso.')
                ->with('class', 'alert-success');
    }

    public function reprovarSolicitaCedula(Request $request)
    {
        $this->solicitaCedulaRepository->updateStatusRecusado($request->id, $request->justificativa, Auth::user()->idusuario);

        event(new CrudEvent('solicitação de cédula alterada', 'atendente recusou e justificou', $request->id));

        return redirect('/admin/solicita-cedula')
                ->with('message', 'A solicitação de cédula foi recusada.')
                ->with('class', 'alert-info');
    }

    public function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Representante',
            'CPF/CNPJ',
            'Registro CORE',
            'Solicitado em:',
            'Atualizado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/solicita-cedula/' . $resultado->id . '" class="btn btn-sm btn-default">Ver</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->representante->nome,
                $resultado->representante->cpf_cnpj,
                $resultado->representante->registro_core,
                formataData($resultado->created_at),
                formataData($resultado->updated_at),
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
                return '<strong><i>'.SolicitaCedula::STATUS_EM_ANDAMENTO.'</i></strong>';
            break;

            case SolicitaCedula::STATUS_RECUSADO:
                return '<strong class="text-danger">'.SolicitaCedula::STATUS_RECUSADO.'</strong>';
            break;

            case SolicitaCedula::STATUS_ACEITO:
                return '<strong class="text-success">'.SolicitaCedula::STATUS_ACEITO.'</strong>';
            break;
            
            default:
                return $string;
            break;
        }
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->resultados();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');

        $busca = IlluminateRequest::input('q');

        $resultados = $this->solicitaCedulaRepository->getBusca($busca);
        
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}
