<?php

namespace App\Http\Controllers;

use App\Http\Requests\BdoEmpresaRequest;
use App\Traits\ControleAcesso;
use App\Repositories\BdoEmpresaRepository;
use App\BdoEmpresa;
use App\BdoOportunidade;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class BdoEmpresaController extends Controller
{
    use ControleAcesso; 

    // Nome da Classe
    private $class = 'BdoEmpresaController';
    private $bdoEmpresa;
    private $bdoEmpresaRepository;
    private $variaveis;

    public function __construct(BdoEmpresa $bdoEmpresa, BdoEmpresaRepository $bdoEmpresaRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'apiGetEmpresa']]);
        $this->bdoEmpresa = $bdoEmpresa;
        $this->bdoEmpresaRepository = $bdoEmpresaRepository;
        $this->variaveis = $bdoEmpresa->variaveis();
    }

    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);

        if(!$this->mostra($this->class, 'create')) {
            unset($this->variaveis['btn_criar']);
        }
            
        $resultados = $this->bdoEmpresaRepository->getToTable();
        $tabela = $this->bdoEmpresa->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $variaveis = (object) $this->variaveis;
        $capitais = $this->bdoEmpresa::capitalSocial();
        $segmentos = $this->bdoEmpresa::segmentos();

        return view('admin.crud.criar', compact('variaveis', 'capitais', 'segmentos'));
    }

    public function store(BdoEmpresaRequest $request)
    {
        $this->autoriza($this->class, 'create');

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
        $this->autoriza($this->class, __FUNCTION__);

        $resultado = $this->bdoEmpresaRepository->findOrFail($id);
        $variaveis = (object) $this->variaveis;
        $capitais = $this->bdoEmpresa::capitalSocial();
        $segmentos = $this->bdoEmpresa::segmentos();

        return view('admin.crud.editar', compact('resultado', 'variaveis', 'capitais', 'segmentos'));
    }

    public function update(BdoEmpresaRequest $request, $id)
    {
        $this->autoriza($this->class, 'edit');

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
        $this->autoriza($this->class, __FUNCTION__);

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
        $this->autoriza($this->class, 'index');

        $busca = IlluminateRequest::input('q');
        $variaveis = (object) $this->variaveis;
        $resultados = $this->bdoEmpresaRepository->busca($busca);
        $tabela = $this->bdoEmpresa->tabelaCompleta($resultados);
        
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    /** Função usada por JQuery na tela de anúncio de vagas */
    public function apiGetEmpresa($cnpj)
    {
        $cnpj = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj);

        $empresa = $this->bdoEmpresaRepository->getToApi($cnpj);

        isset($empresa) ? $count = $empresa->oportunidade_count : $count = 0;

        if($count > 0) {
            $message = 'A empresa informada <strong>já possui uma vaga sob análise ou em andamento no Balcão de Oportunidades</strong> do Core-SP. Para solicitar nova inclusão, favor entrar em contato através do telefone <strong>(11) 3243-5523</strong> e/ou através do e-mail: <strong>informacoes@core-sp.org.br</strong> informando CNPJ, nome do responsável e telefone para contato.';
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
        else {
            abort(500);
        }
    }
}