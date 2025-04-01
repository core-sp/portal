<?php

namespace App\Http\Controllers;

use App\BdoEmpresa;
use App\BdoOportunidade;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use App\Http\Requests\BdoEmpresaRequest;
use App\Repositories\BdoEmpresaRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;
use App\Contracts\MediadorServiceInterface;
use App\Rules\Cnpj;

class BdoEmpresaController extends Controller
{
    use TabelaAdmin; 

    // Nome da Classe
    private $class = 'BdoEmpresaController';
    private $bdoEmpresaRepository;
    private $service;
    private $avisoAtivado;

    private $variaveis = [
        'singular' => 'empresa',
        'singulariza' => 'a empresa',
        'plural' => 'empresas',
        'pluraliza' => 'empresas',
        'titulo_criar' => 'Cadastrar nova empresa',
        'form' => 'bdoempresa',
        'btn_criar' => '<a href="/admin/bdo/empresas/criar" class="btn btn-primary mr-1">Nova Empresa</a>',
        'busca' => 'bdo/empresas',
        'slug' => 'bdo/empresas'
    ];

    public function __construct(BdoEmpresaRepository $bdoEmpresaRepository, MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['show', 'apiGetEmpresa']]);

        $this->bdoEmpresaRepository = $bdoEmpresaRepository;
        $this->service = $service;
        $this->avisoAtivado = $this->service->getService('Aviso')->avisoAtivado($this->service->getService('Aviso')->areas()[1]);
    }

    public function index()
    {
        $this->authorize('viewAny', auth()->user());

        if(auth()->user()->cannot('create', auth()->user())) {
            unset($this->variaveis['btn_criar']);
        }
            
        $resultados = $this->bdoEmpresaRepository->getToTable();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->authorize('create', auth()->user());

        $variaveis = (object) $this->variaveis;
        $capitais = BdoEmpresa::capitalSocial();
        $segmentos = BdoEmpresa::segmentos();

        return view('admin.crud.criar', compact('variaveis', 'capitais', 'segmentos'));
    }

    public function store(BdoEmpresaRequest $request)
    {
        $this->authorize('create', auth()->user());

        $request->validated();

        $save = $this->bdoEmpresaRepository->store($request->toModel());

        if(!$save) {
            abort(500);
        }
            
        event(new CrudEvent('empresa (Balcão de Oportunidades)', 'criou', $save->idempresa));

        return redirect()->route('bdoempresas.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Empresa cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());

        $resultado = $this->bdoEmpresaRepository->findOrFail($id);
        $variaveis = (object) $this->variaveis;
        $capitais = BdoEmpresa::capitalSocial();
        $segmentos = BdoEmpresa::segmentos();

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'capitais', 'segmentos'));
    }

    public function update(BdoEmpresaRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());

        $request->validated();

        $update = $this->bdoEmpresaRepository->update($id, $request->toModel());
        
        if(!$update) {
            abort(500);
        }
            
        event(new CrudEvent('empresa (Balcão de Oportunidades)', 'editou', $id));

        return redirect()->route('bdoempresas.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Empresa editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        $this->authorize('delete', auth()->user());

        $empresa = $this->bdoEmpresaRepository->getOportunidadesbyEmpresa($id);

        if($empresa->oportunidade_count >= 1) {
            return redirect()->route('bdoempresas.lista')
                ->with('message', '<i class="icon fa fa-ban"></i>Não é possível apagar empresas com oportunidades!')
                ->with('class', 'alert-danger');
        } 
        else {
            $delete = $this->bdoEmpresaRepository->destroy($id);

            if(!$delete) {
                abort(500);
            }
                
            event(new CrudEvent('empresa (Balcão de Oportunidades)', 'apagou', $empresa->idempresa));

            return redirect()->route('bdoempresas.lista')
                ->with('message', '<i class="icon fa fa-ban"></i>Empresa deletada com sucesso!')
                ->with('class', 'alert-success');
        }
    }

    public function busca()
    {
        $this->authorize('viewAny', auth()->user());

        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->bdoEmpresaRepository->busca($busca);
        $tabela = $this->tabelaCompleta($resultados);
        
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    /** Função usada por JQuery na tela de anúncio de vagas */
    public function apiGetEmpresa($cnpj)
    {
        if($this->avisoAtivado)
            return [
                'empresa' => '{}',
                'message' => 'Não é possível verificar no momento se a empresa está cadastrada.',
                'class' => 'alert-warning'
            ];

        request()->request->set('cnpj', apenasNumeros($cnpj));
        $this->validate(request(), [
            'cnpj' => ['required', new Cnpj],
        ], [
            'cnpj.required' => 'Informe o CNPJ',
        ]);

        $cnpj = formataCpfCnpj($cnpj);

        $empresa = $this->bdoEmpresaRepository->getToApi($cnpj);

        isset($empresa) ? $count = $empresa->oportunidade_count : $count = 0;

        if($count > 0) {
            $message = 'A empresa informada <strong>já possui uma vaga sob análise ou em andamento no Balcão de Oportunidades</strong> do Core-SP. Para solicitar nova inclusão, favor entrar em contato através do e-mail: <strong>assessoria.presidencia@core-sp.org.br</strong> informando CNPJ, nome do responsável e telefone para contato.';
            $class = 'alert-warning';
        } 
        else {
            $message = 'Empresa já cadastrada. Favor seguir com o preenchimento da oportunidade abaixo.';
            $class = 'alert-success';
        }

        if(isset($empresa)) {
            unset($empresa->oportunidade_count);

            return [
                'empresa' => $empresa->toJson(),
                'message' => $message,
                'class' => $class
            ];
        }
        
        return [];
    }

    public function tabelaCompleta($query)
    {
        $headers = [
            'Código',
            'Segmento',
            'Razão Social',
            'Ações'
        ];

        $contents = $query->map(function($row){
            if(auth()->user()->perfil->temPermissao('BdoOportunidadeController', 'create')) {
                $acoes = '<a href="/admin/bdo/criar/'.$row->idempresa.'" class="btn btn-sm btn-secondary">Nova Oportunidade</a> ';
            }
            else {
                $acoes = '';
            }
               
            if(auth()->user()->can('updateOther', auth()->user())) {
                $acoes .= '<a href="/admin/bdo/empresas/editar/'.$row->idempresa.'" class="btn btn-sm btn-primary">Editar</a> ';
            }
                
            if(auth()->user()->can('delete', auth()->user())) {
                $acoes .= '<form method="POST" action="/admin/bdo/empresas/apagar/'.$row->idempresa.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a empresa?\')" />';
                $acoes .= '</form>';
            }

            if(empty($acoes)) {
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            }
                        
            return [
                $row->idempresa,
                $row->segmento,
                $row->razaosocial,
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