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
use App\Mail\SolicitaCedulaMail;
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
        $cedula = $this->solicitaCedulaRepository->updateStatusAceito($request->id, Auth::user()->idusuario);

        if(!$cedula)
            abort(500);

        $cedula = $this->solicitaCedulaRepository->getById($request->id);

        event(new CrudEvent('solicitação de cédula alterada', 'atendente aceitou', $request->id));
        
        // $cedula->representante->email
        Mail::to("desenvolvimento@core-sp.org.br")->queue(new SolicitaCedulaMail($cedula));

        return redirect('/admin/solicita-cedula')
                ->with('message', 'A solicitação de cédula foi cadastrada com sucesso.')
                ->with('class', 'alert-success');
    }

    public function reprovarSolicitaCedula(Request $request)
    {
        $cedula = $this->solicitaCedulaRepository->updateStatusRecusado($request->id, $request->justificativa, Auth::user()->idusuario);

        if(!$cedula)
            abort(500);

        $cedula = $this->solicitaCedulaRepository->getById($request->id);

        event(new CrudEvent('solicitação de cédula alterada', 'atendente recusou e justificou', $request->id));

        // $cedula->representante->email
        Mail::to("desenvolvimento@core-sp.org.br")->queue(new SolicitaCedulaMail($cedula));

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

    public function confereAplicaFiltros()
    {
        $this->autoriza($this->class, 'index');

        // Valores default dos filtros
        $mindia = date('Y-m-d');
        $maxdia = date('Y-m-d');

        // Valida e prepara filtro de data mínima
        if(IlluminateRequest::has('mindia')) {
            if(!empty(IlluminateRequest::input('mindia'))) {
                $mindiaArray = explode('/', IlluminateRequest::input('mindia'));
                $checaMindia = (count($mindiaArray) != 3 || $mindiaArray[2] == null)  ? false : checkdate($mindiaArray[1], $mindiaArray[0], $mindiaArray[2]);

                if($checaMindia === false) {
                    return redirect()->back()->with('message', '<i class="icon fa fa-ban"></i>Data de início do filtro inválida')
                        ->with('class', 'alert-danger');
                }

                $mindia = date('Y-m-d', strtotime(str_replace('/', '-', IlluminateRequest::input('mindia'))));
            }
        } 

        // Valida e prepara filtro de data máxima
        if(IlluminateRequest::has('maxdia')) {
            if(!empty(IlluminateRequest::input('maxdia'))) {
                $maxdiaArray = explode('/', IlluminateRequest::input('maxdia'));
                $checaMaxdia = (count($maxdiaArray) != 3 || $maxdiaArray[2] == null)  ? false : checkdate($maxdiaArray[1], $maxdiaArray[0], $maxdiaArray[2]);

                if($checaMaxdia === false) {
                    return redirect()->back()->with('message', '<i class="icon fa fa-ban"></i>Data de término do filtro inválida')
                        ->with('class', 'alert-danger');
                }

                $maxdia = date('Y-m-d', strtotime(str_replace('/', '-', IlluminateRequest::input('maxdia'))));
            }         
        } 

        return $this->solicitaCedulaRepository->getToTableFilter($mindia, $maxdia);
    }

    public function montaFiltros()
    {
        $filtro = '<form method="GET" action="/admin/agendamentos/filtro" id="filtroAgendamento" class="mb-0">';
        $filtro .= '<div class="form-row filtroAge">';
        $filtro .= '<input type="hidden" name="filtro" value="sim" />';

        $filtro .= '<div class="form-group mb-0 col">';

        $hoje = date('d\/m\/Y');

        $filtro .= '<label>De</label>';
       
        // Montando filtro de data mínima
        if(IlluminateRequest::has('mindia')) {
            $mindia = IlluminateRequest::input('mindia');
            $filtro .= '<input type="date" class="form-control d-inline-block dataInput form-control-sm" name="mindia" id="mindiaFiltro" placeholder="dd/mm/aaaa" value="' . $mindia . '" />';
        } 
        else {
            $filtro .= '<input type="date" class="form-control d-inline-block dataInput form-control-sm" name="mindia" id="mindiaFiltro" placeholder="dd/mm/aaaa" value="' . $hoje . '" />';
        }

        $filtro .= '</div>';
        $filtro .= '<div class="form-group mb-0 col">';
        $filtro .= '<label>Até</label>';
        
        // Montando filtro de data máxima
        if(IlluminateRequest::has('maxdia')) {
            $maxdia = IlluminateRequest::input('maxdia');
            $filtro .= '<input type="date" class="form-control d-inline-block dataInput form-control-sm" name="maxdia" id="maxdiaFiltro" placeholder="dd/mm/aaaa" value="' . $maxdia . '" />';
        } 
        else {
            $filtro .= '<input type="date" class="form-control d-inline-block dataInput form-control-sm" name="maxdia" id="maxdiaFiltro" placeholder="dd/mm/aaaa" value="' . $hoje . '" />';
        }

        $filtro .= '</div>';
        $filtro .= '<div class="form-group mb-0 col-auto align-self-end">';
        $filtro .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
        $filtro .= '</div>';
        $filtro .= '</div>';
        $filtro .= '</form>';

        return $filtro;
    }
}
