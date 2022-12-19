<?php

namespace App\Http\Controllers;

// use App\Events\CrudEvent;
// use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
use App\Http\Requests\SolicitaCedulaRequest;
// use App\Repositories\SolicitaCedulaRepository;
// use App\Mail\SolicitaCedulaMail;
// use App\Repositories\GerentiRepositoryInterface;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Http\RedirectResponse;
// use Carbon\Carbon;
// use PDF;
// use Illuminate\Support\Facades\Request as IlluminateRequest;
use App\Contracts\MediadorServiceInterface;

class SolicitaCedulaController extends Controller
{
    // use TabelaAdmin;

    // private $class = 'SolicitaCedulaController';
    // private $solicitaCedulaRepository;
    // private $gerentiRepository;
    private $service;

    // // Variáveis
    // private $variaveis = [
    //     'singular' => 'solicitação de cédula',
    //     'singulariza' => 'a solicitação de cédula',
    //     'plural' => 'solicitações de cédulas',
    //     'pluraliza' => 'solicitações de cédulas',
    //     'mostra' => 'solicita-cedula',
    //     'slug' => 'solicita-cedula',
    //     'busca' => 'solicita-cedulas'
    // ];

    public function __construct(/*SolicitaCedulaRepository $solicitaCedulaRepository, GerentiRepositoryInterface $gerentiRepository, */MediadorServiceInterface $service)
    {
        $this->middleware('auth');
        // $this->solicitaCedulaRepository = $solicitaCedulaRepository;
        // $this->gerentiRepository = $gerentiRepository;
        $this->service = $service;
    }

    public function show($id)
    {
        $this->authorize('updateShow', auth()->user());
        // $resultado = $this->solicitaCedulaRepository->getById($id);
        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Cedula')->view($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para visualizar a solicitação de cédula.");
        }

        return view('admin.crud.mostra')->with($dados);
    }

    public function updateStatus(SolicitaCedulaRequest $request, $id)
    {
        $this->authorize('updateShow', auth()->user());
        // if(auth()->user() == null)
        //     abort(500, "Usuário não encontrado");
        try {
        //     $cedula = $this->solicitaCedulaRepository->updateStatusAceito($request->id, auth()->user()->idusuario);
        //     $cedula = $this->solicitaCedulaRepository->getById($request->id);

        //     event(new CrudEvent('solicitação de cédula', 'atendente aceitou', $request->id));
            
        //     Mail::to($cedula->representante->email)->queue(new SolicitaCedulaMail($cedula));
            $validated = $request->validated();
            $dados = $this->service->getService('Cedula')->updateStatus($id, $validated, auth()->user());
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao atualizar o status da solicitação de cédula.");
        }

        $txt = isset($validated['justificativa']) ? 'recusada.' : 'aceita.';

        return redirect()->route('solicita-cedula.index')->with([
            'message' => '<i class="fas fa-check"></i> A solicitação de cédula com a ID: ' . $id . ' foi ' . $txt,
            'class' => 'alert-success'
        ]);
    }

    // public function reprovarSolicitaCedula(SolicitaCedulaRequest $request)
    // {
    //     $this->authorize('updateShow', auth()->user());
    //     $request->validated();

    //     if(auth()->user() == null)
    //         abort(500, "Usuário não encontrado");
    //     try {
    //         $cedula = $this->solicitaCedulaRepository->updateStatusRecusado($request->id, $request->justificativa, auth()->user()->idusuario);
    //         $cedula = $this->solicitaCedulaRepository->getById($request->id);

    //         event(new CrudEvent('solicitação de cédula', 'atendente recusou e justificou', $request->id));

    //         Mail::to($cedula->representante->email)->queue(new SolicitaCedulaMail($cedula));
    //     } catch (\Exception $e) {
    //         \Log::error($e->getMessage());
    //         abort(500, "Erro ao atualizar o status da solicitação de cédula.");
    //     }
    //     return redirect(route('solicita-cedula.index'))
    //             ->with('message', '<i class="fas fa-check"></i> A solicitação de cédula foi recusada.')
    //             ->with('class', 'alert-success');
    // }

    // private function tabelaCompleta($resultados)
    // {
    //     // Opções de cabeçalho da tabela
    //     $headers = [
    //         'Código',
    //         'Representante',
    //         'CPF/CNPJ',
    //         'Registro CORE',
    //         'Regional',
    //         'Solicitado em:',
    //         'Atualizado em:',
    //         'Status',
    //         'Ações'
    //     ];
    //     // Opções de conteúdo da tabela
    //     $contents = [];
    //     foreach($resultados as $resultado) {
    //         $acoes = '<a href="'. route('admin.solicita-cedula.show', $resultado->id) . '" class="btn btn-sm btn-default">Ver</a> ';
    //         if($resultado->podeGerarPdf())
    //             $acoes .= '<a href="' . route('admin.solicita-cedula.pdf', $resultado->id) . '" target="_blank" class="btn btn-sm btn-warning">PDF</a> ';
    //         $conteudo = [
    //             $resultado->id,
    //             $resultado->representante->nome,
    //             $resultado->representante->cpf_cnpj,
    //             $resultado->representante->registro_core,
    //             $resultado->regional->regional,
    //             formataData($resultado->created_at),
    //             formataData($resultado->updated_at),
    //             '<strong class="' .$resultado->showStatus(). '">' .$resultado->status. '</strong>',
    //             $acoes
    //         ];
    //         array_push($contents, $conteudo);
    //     }
    //     // Classes da tabela
    //     $classes = [
    //         'table',
    //         'table-hover'
    //     ];

    //     // Monta e retorna tabela        
    //     $tabela = $this->montaTabela($headers, $contents, $classes);
    //     return $tabela;

    // }

    public function index(Request $request)
    {
        $this->authorize('viewAny', auth()->user());
        // $variaveis = $this->variaveis;

        // // Checa se tem filtro
        // if(IlluminateRequest::input('filtro') === 'sim') {
        //     $temFiltro = true;
        //     $variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
        //     $variaveis['plural'] = 'solicita-cedula';
        //     $resultados = $this->checaAplicaFiltros();

        //     if($resultados instanceof RedirectResponse) {
        //         return $resultados;
        //     }
        // }else {
        //     $temFiltro = null;
        //     $resultados = $this->solicitaCedulaRepository->getAll();
        // }
        
        // $tabela = $this->tabelaCompleta($resultados);
        // $variaveis['filtro'] = $this->montaFiltros();
        // $variaveis['mostraFiltros'] = true;
        // $variaveis = (object) $variaveis;

        try{
            $dados = $this->service->getService('Cedula')->listar($request);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as solicitações de cédulas.");
        }

        return view('admin.crud.home')->with($dados);

        // return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados', 'temFiltro'));
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());
        // $busca = IlluminateRequest::input('q');

        // // Verifica se o texto buscado contem numero para remover a máscara
        // $busca = preg_match('/\d+/', $busca) > 0 ? apenasNumeros($busca) : $busca;
        // $resultados = $this->solicitaCedulaRepository->getBusca($busca);
        // $tabela = $this->tabelaCompleta($resultados);
        // $variaveis = (object) $this->variaveis;

        try{
            $dados = $this->service->getService('Cedula')->buscar($request->q);
            $dados['busca'] = $request->q;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em solicitações de cédulas.");
        }

        return view('admin.crud.home')->with($dados);
    }

    // private function checaAplicaFiltros()
    // {
    //     $this->authorize('viewAny', auth()->user());
    //     $result;

    //     // Confere se a data consta no request
    //     if(IlluminateRequest::has('mindia') && IlluminateRequest::has('maxdia')) {
    //         // Confere se a data de início é menor que a do término
    //         try {
    //             $mindia = Carbon::createFromFormat('Y-m-d', IlluminateRequest::input('mindia'));
    //             $maxdia = Carbon::createFromFormat('Y-m-d', IlluminateRequest::input('maxdia'));
    //             $result = $mindia->lte($maxdia) ? $this->solicitaCedulaRepository->getToTableFilter($mindia->toDateString(), $maxdia->toDateString()) : null;
    //         } catch(\Exception $err) {
    //             $result = null;
    //         }
    //     }

    //     if(isset($result))
    //         return $result;

    //     return redirect()->back()
    //     ->with('message', '<i class="fas fa-ban"></i> Data inválida. Data de início deve ser menor ou igual a data do término.')
    //     ->with('class', 'alert-danger');
    // }

    // private function montaFiltros()
    // {
    //     $filtro = '<form method="GET" action="' . route('solicita-cedula.filtro') . '" id="filtroCedula" class="mb-0">';
    //     $filtro .= '<div class="form-row filtroAge">';
    //     $filtro .= '<input type="hidden" name="filtro" value="sim" />';
    //     $filtro .= '<div class="form-group mb-0 col">';
    //     $filtro .= '<label for="datemin">Solicitado em</label>';
       
    //     // Montando filtro de data mínima
    //     if(IlluminateRequest::has('mindia')) {
    //         $mindia = IlluminateRequest::input('mindia');
    //         $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="mindia" id="datemin" min="2021-08-01" value="' . $mindia . '" />';
    //     } 
    //     else {
    //         $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="mindia" id="datemin" min="2021-08-01"/>';
    //     }

    //     $filtro .= '</div>';
    //     $filtro .= '<div class="form-group mb-0 col">';
    //     $filtro .= '<label for="datemax">até</label>';
        
    //     // Montando filtro de data máxima
    //     if(IlluminateRequest::has('maxdia')) {
    //         $maxdia = IlluminateRequest::input('maxdia');
    //         $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="maxdia" id="datemax" max="3000-08-01" value="' . $maxdia . '" />';
    //     } 
    //     else {
    //         $filtro .= '<input type="date" class="form-control d-inline-block form-control-sm" name="maxdia" id="datemax" max="3000-08-01"/>';
    //     }

    //     $filtro .= '</div>';
    //     $filtro .= '<div class="form-group mb-0 col-auto align-self-end">';
    //     $filtro .= '<input type="submit" class="btn btn-sm btn-default" value="Filtrar" />';
    //     $filtro .= '</div>';
    //     $filtro .= '</div>';
    //     $filtro .= '</form>';

    //     return $filtro;
    // }

    public function gerarPdf($id)
    {
        $this->authorize('updateShow', auth()->user());
        // $resultado = $this->solicitaCedulaRepository->getById($id);
        // if($resultado->podeGerarPdf())
        // {
        //     $pdf = PDF::loadView('admin.forms.cedulaPDF', compact('resultado'))->setWarnings(false);
        //     return $pdf->stream('cedula_codigo_'.$id.'.pdf');
        // }

        try{
            $dados = $this->service->getService('Cedula')->gerarPdf($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao gerar o pdf da solicitação de cédula.");
        }

        if(isset($dados['stream']))
            return $dados['stream'];

        return redirect()->route('solicita-cedula.index')->with([
            'message' => '<i class="fas fa-ban"></i> A solicitação de cédula não foi aceita.',
            'class' => 'alert-danger'
        ]);
    }
}
