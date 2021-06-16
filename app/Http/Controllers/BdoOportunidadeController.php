<?php

namespace App\Http\Controllers;

use App\BdoEmpresa;
use App\BdoOportunidade;
use App\Events\CrudEvent;
use App\Traits\TabelaAdmin;
use App\Traits\ControleAcesso;
use App\Repositories\RegionalRepository;
use App\Repositories\BdoEmpresaRepository;
use App\Http\Requests\BdoOportunidadeRequest;
use App\Repositories\BdoOportunidadeRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class BdoOportunidadeController extends Controller
{
    use ControleAcesso, TabelaAdmin;

    // Nome da Classe
    private $class = 'BdoOportunidadeController';
    private $bdoOportunidadeRepository;
    private $bdoEmpresaRepository;
    private $regionalRepository;

    private $bdoOportunidadeVariaveis = [
        'singular' => 'oportunidade',
        'singulariza' => 'a oportunidade',
        'plural' => 'oportunidade',
        'pluraliza' => 'oportunidades',
        'titulo_criar' => 'Cadastrar nova oportunidade',
        'form' => 'bdooportunidade',
        'busca' => 'bdo',
        'slug' => 'bdo'
    ];

    public function __construct(BdoOportunidadeRepository $bdoOportunidadeRepository, BdoEmpresaRepository $bdoEmpresaRepository, RegionalRepository $regionalRepository)
    {
        $this->middleware('auth', ['except' => 'show']);

        $this->bdoOportunidadeRepository = $bdoOportunidadeRepository;
        $this->bdoEmpresaRepository = $bdoEmpresaRepository;
        $this->regionalRepository = $regionalRepository;
    }
    
    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->bdoOportunidadeRepository->getToTable();
        $tabela = $this->tabelaCompleta($resultados);
        $variaveis = (object) $this->bdoOportunidadeVariaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create($id)
    {       
        $this->autoriza($this->class, __FUNCTION__);

        $empresa = $this->bdoEmpresaRepository->getToOportunidade($id);

        $regioes = $this->regionalRepository->getRegionais();
        
        if (isset($empresa)) {
            $variaveis = (object) $this->bdoOportunidadeVariaveis;
            $status = BdoOportunidade::status();
            $segmentos = BdoEmpresa::segmentos();

            return view('admin.crud.criar', compact('empresa', 'regioes', 'variaveis', 'status', 'segmentos'));
        } 
        else {
            abort(401);
        }
    }

    public function store(BdoOportunidadeRequest $request)
    {
        $this->autoriza($this->class, 'create');

        $request->validated();

        $save = $this->bdoOportunidadeRepository->store($request->toModel());

        if(!$save) {
            abort(500);
        }
            
        event(new CrudEvent('oportunidade (Balcão de Oportunidades)', 'criou', $save->idoportunidade));

        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Oportunidade cadastrada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->bdoOportunidadeRepository->findOrFail($id);
        $regioes = $this->regionalRepository->getRegionais();
        $regioesEdit = explode(',', $resultado->regiaoatuacao);
        $variaveis = (object) $this->bdoOportunidadeVariaveis;
        $status = BdoOportunidade::status();
        $segmentos = BdoEmpresa::segmentos();

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'regioes', 'regioesEdit', 'status', 'segmentos'));
    }

    public function update(BdoOportunidadeRequest $request, $id)
    {
        $this->autoriza($this->class, 'edit');

        $request->validated();

        $update = $this->bdoOportunidadeRepository->update($id, $request->toModel());

        if(!$update) {
            abort(500);
        }
           
        event(new CrudEvent('oportunidade (Balcão de Oportunidades)', 'editou', $id));

        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-check"></i>Oportunidade editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function destroy($id)
    {
        $this->autoriza($this->class, __FUNCTION__);

        $delete = $this->bdoOportunidadeRepository->destroy($id);

        if(!$delete) {
            abort(500);
        }
            
        event(new CrudEvent('oportunidade (Balcão de Oportunidades)', 'apagou', $id));

        return redirect()->route('bdooportunidades.lista')
            ->with('message', '<i class="icon fa fa-ban"></i>Oportunidade deletada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca()
    {
        $this->autoriza($this->class, 'index');

        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->bdoOportunidadeVariaveis;
        $resultados = $this->bdoOportunidadeRepository->busca($busca);
        $tabela = $this->tabelaCompleta($resultados);

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function tabelaCompleta($query)
    {
        $headers = [
            'Código',
            'Empresa',
            'Segmento',
            'Vagas',
            'Status',
            'Ações'
        ];

        $contents = $query->map(function($row){
            if($this->mostra('BdoOportunidadeController', 'edit')) {
                $acoes = '<a href="/admin/bdo/editar/'.$row->idoportunidade.'" class="btn btn-sm btn-primary">Editar</a> ';
            }     
            else {
                $acoes = '';
            }

            if($this->mostra('BdoOportunidadeController', 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/bdo/apagar/'.$row->idoportunidade.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir a oportunidade?\')" />';
                $acoes .= '</form>';
            }

            if(empty($acoes)) {
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            }

            if(isset($row->vagaspreenchidas)) {
                $relacaovagas = $row->vagaspreenchidas.' / '.$row->vagasdisponiveis;
            }     
            else {
                $relacaovagas = 'X / '.$row->vagasdisponiveis;
            }
                
            if(isset($row->empresa->razaosocial)) {
                $razaosocial = $row->empresa->razaosocial;
            }     
            else {
                $razaosocial = '';
            }
                        
            return [
                $row->idoportunidade,
                $razaosocial,
                $row->segmento,
                $relacaovagas,
                BdoOportunidade::statusDestacado($row->status),
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