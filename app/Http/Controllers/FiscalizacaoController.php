<?php

namespace App\Http\Controllers;

// use App\PeriodoFiscalizacao;
// use App\Events\CrudEvent;
// use App\Traits\TabelaAdmin;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use App\Repositories\FiscalizacaoRepository;
use App\Http\Requests\PeriodoFiscalizacaoRequest;
use App\Contracts\MediadorServiceInterface;
// use Illuminate\Support\Facades\Request as IlluminateRequest;

class FiscalizacaoController extends Controller
{
    // use TabelaAdmin;

    // private $class = 'FiscalizacaoController';
    private $service;
    // private $fiscalizacaoRepository;
    
    // Variáveis para páginas no Admin
    // private $periodoFiscalizacaoVariaveis;
    // private $dadosFiscalizacaoVariaveis;

    public function __construct(MediadorServiceInterface $service/*, FiscalizacaoRepository $fiscalizacaoRepository*/)
    {
        $this->middleware('auth', ['except' => ['mostrarMapa', 'mostrarMapaPeriodo']]);

        $this->service = $service;
        // $this->fiscalizacaoRepository = $fiscalizacaoRepository;

        // $this->periodoFiscalizacaoVariaveis = [
        //     'singular' => 'ano de fiscalização',
        //     'singulariza' => 'o ano de fiscalização',
        //     'plural' => 'anos de fiscalização',
        //     'pluraliza' => 'anos de fiscalização',
        //     'titulo_criar' => 'Cria ano de fiscalização',
        //     'busca' => 'fiscalizacao',
        //     'slug' => 'fiscalizacao'
        // ];

        // $this->dadosFiscalizacaoVariaveis = [
        //     'singular' => 'dado de fiscalização',
        //     'singulariza' => 'o dado de fiscalização',
        //     'plural' => 'dados de fiscalização',
        //     'pluraliza' => 'dados de fiscalização',
        //     'titulo_criar' => 'Registrar dados de fiscalização',
        //     'form' => 'dadofiscalizacao'
        // ];
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        // $resultados = $this->fiscalizacaoRepository->getAll();
        // $tabela = $this->tabelaCompleta($resultados);
        // $variaveis = (object) $this->periodoFiscalizacaoVariaveis;

        try{
            $dados = $this->service->getService('Fiscalizacao')->listar();
            $variaveis = $dados['variaveis'];
            $tabela = $dados['tabela'];
            $resultados = $dados['resultados'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os períodos da fiscalização.");
        }

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function createPeriodo() 
    {   
        $this->authorize('create', auth()->user());

        // $regionais = $this->service->getService('Regional')->getRegionais();
        // $variaveis = $this->periodoFiscalizacaoVariaveis;
        // $variaveis['form'] = 'periodofiscalizacaocreate';
        // $variaveis = (object) $variaveis;

        try{
            $dados = $this->service->getService('Fiscalizacao')->view();
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para criar o período da fiscalização.");
        }

        return view('admin.crud.criar', compact('variaveis'));
    }

    public function storePeriodo(PeriodoFiscalizacaoRequest $request)
    {
        $this->authorize('create', auth()->user());

        // DB::transaction(function () use ($request) {
        //     $periodo = $this->fiscalizacaoRepository->storePeriodo($request->toModel());

        //     $regionais = $this->service->getService('Regional')->getRegionais();

        //     foreach($regionais as $regional) {
        //         $this->fiscalizacaoRepository->storeDadoFiscalizacao($regional->idregional, $periodo->id);
        //     }
        // });

        try{
            $validated = $request->validated();
            $this->service->getService('Fiscalizacao')->save($validated, $this->service);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao criar o período da fiscalização.");
        }

        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>O ano foi criado com sucesso')
            ->with('class', 'alert-success');
    }

    public function updateStatus(/*Request $request*/$id)
    {
        $this->authorize('updateOther', auth()->user());

        // $idusuario = Auth::user()->idusuario;
        // $idperiodo = $request->idperiodo;
        // $status = $request->status;

        // $update = $this->fiscalizacaoRepository->updatePeriodoStatus($idperiodo, ['status' => $status]);

        // if(!$update) 
        // {
        //     abort(500);
        // }

        // if($status) 
        // {
        //     event(new CrudEvent('ano de fiscalização', 'publicou período com ID', $idperiodo));
        // } 
        // else 
        // {
        //     event(new CrudEvent('ano de fiscalização', 'reverteu publicação do período com ID', $idperiodo));
        // }

        try{
            $this->service->getService('Fiscalizacao')->updateStatus($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao atualizar o status do período da fiscalização.");
        }
        
        return redirect()->route('fiscalizacao.index')
            ->with('message', '<i class="icon fa fa-check"></i>Status do período com a ID: ' . $id . ' foi atualizado com sucesso!')
            ->with('class', 'alert-success');
    }

    public function editPeriodo($id)
    {
        $this->authorize('updateOther', auth()->user());

        // $resultado = $this->fiscalizacaoRepository->findOrFail($id);
        // $variaveis = $this->periodoFiscalizacaoVariaveis;
        // $variaveis['form'] = 'periodofiscalizacaoedit';
        // $variaveis = (object) $variaveis;

        try{
            $dados = $this->service->getService('Fiscalizacao')->view($id);
            $variaveis = $dados['variaveis'];
            $resultado = $dados['resultado'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar os dados para editar o período da fiscalização.");
        }

        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }


    public function updatePeriodo(PeriodoFiscalizacaoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        // $dadosFiscalizacao = $request->regional;

        // $this->validate($request, [
        //     'regional.*.*' => 'required|integer|min:0|max:999999999'
        // ], [
        //     'required' => 'Informe o valor',
        //     'min' => 'Valor deve ser maior ou igual a 0',
        //     'max' => 'Valor deve ser menor ou igual a 999999999',
        //     'integer' => 'Valor deve ser um inteiro',
        // ]);

        // DB::transaction(function () use ($dadosFiscalizacao, $id) {
        //     $periodo = $this->fiscalizacaoRepository->updateDadoFiscalizacao($dadosFiscalizacao, $id);
        // });

        try{
            $validated = $request->validated();
            $erro = $this->service->getService('Fiscalizacao')->save($validated, null, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao editar os dados do período da fiscalização.");
        }

        $message = isset($erro['message']) ? $erro['message'] : '<i class="icon fa fa-check"></i>O período com a ID: ' . $id . ' foi editado com sucesso';
        $class = isset($erro['class']) ? $erro['class'] : 'alert-success';

        return redirect()->route('fiscalizacao.index')
            ->with('message', $message)
            ->with('class', $class);
    }

    public function busca(Request $request)
    {
        $this->authorize('viewAny', auth()->user());

        // $busca = IlluminateRequest::input('q');
    
        // $resultados = $this->fiscalizacaoRepository->busca($busca);

        // $tabela = $this->tabelaCompleta($resultados);
        // $variaveis = (object) $this->periodoFiscalizacaoVariaveis;

        try{
            $busca = $request->q;
            $dados = $this->service->getService('Fiscalizacao')->buscar($busca);
            $resultados = $dados['resultados'];
            $tabela = $dados['tabela'];
            $variaveis = $dados['variaveis'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em fiscalização.");
        }

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function mostrarMapa()
    {
        // $todosPeriodos = $this->fiscalizacaoRepository->getPublicado();
        // $periodoSelecionado = $todosPeriodos->first();
        // $todosPeriodos = $todosPeriodos->count() == 0 ? null : $todosPeriodos;
        // $dataAtualizacao = $periodoSelecionado ? onlyDate($periodoSelecionado->dadoFiscalizacao->sortByDesc("updated_at")->first()->updated_at) : null;

        try{
            $dados = $this->service->getService('Fiscalizacao')->mapaSite();
            $todosPeriodos = $dados['todosPeriodos'];
            $periodoSelecionado = $dados['periodoSelecionado'];
            $dataAtualizacao = $dados['dataAtualizacao'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o mapa da fiscalização.");
        }

        return view('site.mapa-fiscalizacao', compact('todosPeriodos', 'periodoSelecionado', 'dataAtualizacao'));
    }

    public function mostrarMapaPeriodo($id)
    {
        // $todosPeriodos = $this->fiscalizacaoRepository->getPublicado();
        // $periodoSelecionado = $todosPeriodos->find($id);

        // if(!$periodoSelecionado) {
        //     return redirect()->route('fiscalizacao.mapa');
        // }

        // $todosPeriodos = $todosPeriodos->count() == 0 ? null : $todosPeriodos;
        // $dataAtualizacao = $periodoSelecionado ? onlyDate($periodoSelecionado->dadoFiscalizacao->sortByDesc("updated_at")->first()->updated_at) : null;

        try{
            $dados = $this->service->getService('Fiscalizacao')->mapaSite($id);
            $todosPeriodos = $dados['todosPeriodos'];
            $periodoSelecionado = $dados['periodoSelecionado'];
            $dataAtualizacao = $dados['dataAtualizacao'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o mapa da fiscalização.");
        }

        return view('site.mapa-fiscalizacao', compact('todosPeriodos', 'periodoSelecionado', 'dataAtualizacao'));
    }

    // public function tabelaCompleta($resultados)
    // {
    //     // Opções de cabeçalho da tabela
    //     $headers = [
    //         'Ano',
    //         'Status',
    //         'Ações'
    //     ];

    //     // Conteúdo da tabela
    //     $contents =  $resultados->map(function($row) {
    //         $acoes = "<form method='POST' id='statusAgendamento' action='" . route('fiscalizacao.updatestatus') . "' class='d-inline'>";
    //         $acoes .= "<input type='hidden' name='idperiodo' value='$row->id'/>";
    //         $acoes .= "<input type='hidden' name='_token' value='" . csrf_token() . "'/>";
            
    //         if($row->status) {
    //             if(auth()->user()->can('updateOther', auth()->user())) {
    //                 $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-danger ml-1' value='0'>Reverter Publicação</button>";
    //             }
                 
    //             $status = PeriodoFiscalizacao::STATUS_PUBLICADO;
    //         }
    //         else {
    //             if(auth()->user()->can('updateOther', auth()->user())) {
    //                 $acoes .= "<button type='submit' name='status' class='btn btn-sm btn-primary' value='1'>Publicar</button>";
    //             }
                
    //             $status = PeriodoFiscalizacao::STATUS_NAO_PUBLICADO;
    //         }

    //         $acoes .= "</form>";

    //         if(auth()->user()->can('updateOther', auth()->user())) {
    //             $acoes .= " <a href='" . route('fiscalizacao.editperiodo', $row->id) . "' class='btn btn-sm btn-default'>Editar</a>";
    //         }
            
    //         return [
    //             $row->periodo,
    //             $status,
    //             $acoes
    //         ];
    //     })->toArray();

    //     // Classes da tabela
    //     $classes = [
    //         'table',
    //         'table-bordered',
    //         'table-striped'
    //     ];
        
    //     return $this->montaTabela($headers, $contents, $classes);
    // }
}
