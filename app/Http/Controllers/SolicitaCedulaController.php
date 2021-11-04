<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Http\Requests\SolicitaCedulaRequest;
use App\SolicitaCedula;
use App\Traits\ControleAcesso;
use App\Repositories\SolicitaCedulaRepository;
use App\Mail\SolicitaCedulaMail;
use App\Repositories\GerentiRepositoryInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use PDF;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class SolicitaCedulaController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    private $class = 'SolicitaCedulaController';
    private $solicitaCedulaRepository;
    private $gerentiRepository;

    // Variáveis
    private $variaveis = [
        'singular' => 'solicitação de cédula',
        'singulariza' => 'a solicitação de cédula',
        'plural' => 'solicitações de cédulas',
        'pluraliza' => 'solicitações de cédulas',
        'mostra' => 'solicita-cedula',
        'slug' => 'solicita-cedula',
        'busca' => 'solicita-cedulas'
    ];

    public function __construct(SolicitaCedulaRepository $solicitaCedulaRepository, GerentiRepositoryInterface $gerentiRepository)
    {
        $this->middleware('auth');
        $this->solicitaCedulaRepository = $solicitaCedulaRepository;
        $this->gerentiRepository = $gerentiRepository;
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
        $this->autoriza($this->class, 'show');
        if(Auth::user() == null)
            abort(500, "Usuário não encontrado");
        try {
            $cedula = $this->solicitaCedulaRepository->updateStatusAceito($request->id, Auth::user()->idusuario);
            $cedula = $this->solicitaCedulaRepository->getById($request->id);

            event(new CrudEvent('solicitação de cédula', 'atendente aceitou', $request->id));
            
            // Mail::to($cedula->representante->email)->queue(new SolicitaCedulaMail($cedula));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao atualizar o status da solicitação de cédula.");
        }

        return redirect(route('solicita-cedula.index'))
                ->with('message', '<i class="fas fa-check"></i> A solicitação de cédula foi aceita.')
                ->with('class', 'alert-success');
    }

    public function reprovarSolicitaCedula(SolicitaCedulaRequest $request)
    {
        $this->autoriza($this->class, 'show');
        $request->validated();

        if(Auth::user() == null)
            abort(500, "Usuário não encontrado");
        try {
            $cedula = $this->solicitaCedulaRepository->updateStatusRecusado($request->id, $request->justificativa, Auth::user()->idusuario);
            $cedula = $this->solicitaCedulaRepository->getById($request->id);

            event(new CrudEvent('solicitação de cédula', 'atendente recusou e justificou', $request->id));

            // Mail::to($cedula->representante->email)->queue(new SolicitaCedulaMail($cedula));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao atualizar o status da solicitação de cédula.");
        }
        return redirect(route('solicita-cedula.index'))
                ->with('message', '<i class="fas fa-check"></i> A solicitação de cédula foi recusada.')
                ->with('class', 'alert-success');
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Representante',
            'CPF/CNPJ',
            'Registro CORE',
            'Regional',
            'Solicitado em:',
            'Atualizado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="'. route('admin.solicita-cedula.show', $resultado->id) . '" class="btn btn-sm btn-default">Ver</a> ';
            if($resultado->podeGerarPdf())
                $acoes .= '<a href="' . route('admin.solicita-cedula.pdf', $resultado->id) . '" class="btn btn-sm btn-warning">PDF</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->representante->nome,
                $resultado->representante->cpf_cnpj,
                $resultado->representante->registro_core,
                $resultado->regional->regional,
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
        $variaveis = $this->variaveis;

        // Checa se tem filtro
        if(IlluminateRequest::input('filtro') === 'sim') {
            $temFiltro = true;
            $variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
            $variaveis['plural'] = 'solicita-cedula';
            $resultados = $this->checaAplicaFiltros();

            if($resultados instanceof RedirectResponse) {
                return $resultados;
            }
        }else {
            $temFiltro = null;
            $resultados = $this->solicitaCedulaRepository->getAll();
        }
        
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis['filtro'] = $this->montaFiltros();
        $variaveis['mostraFiltros'] = true;
        $variaveis = (object) $variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');
        $busca = IlluminateRequest::input('q');

        // Verifica se o texto buscado contem numero para remover a máscara
        $busca = preg_match('/\d+/', $busca) > 0 ? apenasNumeros($busca) : $busca;
        $resultados = $this->solicitaCedulaRepository->getBusca($busca);
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    private function checaAplicaFiltros()
    {
        $this->autoriza($this->class, 'index');
        $result;

        // Confere se a data consta no request
        if(IlluminateRequest::has('mindia') && IlluminateRequest::has('maxdia')) {
            // Confere se a data de início é menor que a do término
            try {
                $mindia = Carbon::createFromFormat('Y-m-d', IlluminateRequest::input('mindia'));
                $maxdia = Carbon::createFromFormat('Y-m-d', IlluminateRequest::input('maxdia'));
                $result = $mindia->lte($maxdia) ? $this->solicitaCedulaRepository->getToTableFilter($mindia->toDateString(), $maxdia->toDateString()) : null;
            } catch(\Exception $err) {
                $result = null;
            }
        }

        if(isset($result))
            return $result;

        return redirect()->back()
        ->with('message', '<i class="fas fa-ban"></i> Data inválida. Data de início deve ser menor ou igual a data do término.')
        ->with('class', 'alert-danger');
    }

    private function montaFiltros()
    {
        $filtro = '<form method="GET" action="' . route('solicita-cedula.filtro') . '" id="filtroCedula" class="mb-0">';
        $filtro .= '<div class="form-row filtroAge">';
        $filtro .= '<input type="hidden" name="filtro" value="sim" />';
        $filtro .= '<div class="form-group mb-0 col">';
        $filtro .= '<label for="datemin">Solicitado em</label>';
       
        // Montando filtro de data mínima
        if(IlluminateRequest::has('mindia')) {
            $mindia = IlluminateRequest::input('mindia');
            $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="mindia" id="datemin" min="2021-08-01" value="' . $mindia . '" />';
        } 
        else {
            $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="mindia" id="datemin" min="2021-08-01"/>';
        }

        $filtro .= '</div>';
        $filtro .= '<div class="form-group mb-0 col">';
        $filtro .= '<label for="datemax">até</label>';
        
        // Montando filtro de data máxima
        if(IlluminateRequest::has('maxdia')) {
            $maxdia = IlluminateRequest::input('maxdia');
            $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="maxdia" id="datemax" max="3000-08-01" value="' . $maxdia . '" />';
        } 
        else {
            $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="maxdia" id="datemax" max="3000-08-01"/>';
        }

        $filtro .= '</div>';
        $filtro .= '<div class="form-group mb-0 col-auto align-self-end">';
        $filtro .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
        $filtro .= '</div>';
        $filtro .= '</div>';
        $filtro .= '</form>';

        return $filtro;
    }

    public function gerarPdf($id)
    {
        $this->autoriza($this->class, 'show');
        $resultado = $this->solicitaCedulaRepository->getById($id);
        if($resultado->podeGerarPdf())
        {
            // pegar identidade PF no Gerenti e PJ ????
            $identidade = $resultado->representante->tipoPessoa() == 'PF' ? 
            $this->gerentiRepository->gerentiDadosGeraisPF($resultado->representante->ass_id)['identidade'] : 
            $this->gerentiRepository->gerentiDadosGeraisPJ($resultado->representante->ass_id)['Inscrição estadual'];
            $pdf = PDF::loadView('admin.forms.cedulaPDF', compact('resultado', 'identidade'))->setWarnings(false);
            return $pdf->stream('cedula_codigo_'.$id.'.pdf');
        }
        return redirect(route('solicita-cedula.index'))
                ->with('message', '<i class="fas fa-ban"></i> A cédula não foi aceita.')
                ->with('class', 'alert-danger');
    }
}
