<?php

namespace App\Http\Controllers;

use App\BdoEmpresa;
use App\Http\Requests\BdoOportunidadeRequest;
use App\Traits\ControleAcesso;
use App\Repositories\BdoOportunidadeRepository;
use App\Repositories\BdoEmpresaRepository;
use App\Repositories\RegionalRepository;
use App\BdoOportunidade;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class BdoOportunidadeController extends Controller
{
    use ControleAcesso;

    // Nome da Classe
    private $class = 'BdoOportunidadeController';
    private $bdoOportunidade;
    private $bdoOportunidadeRepository;
    private $bdoOportunidadeVariaveis;
    private $bdoEmpresa;
    private $bdoEmpresaRepository;
    private $regionalRepository;

    public function __construct(BdoOportunidade $bdoOportunidade, BdoOportunidadeRepository $bdoOportunidadeRepository, BdoEmpresaRepository $bdoEmpresaRepository, RegionalRepository $regionalRepository, BdoEmpresa $bdoEmpresa)
    {
        $this->middleware('auth', ['except' => 'show']);

        $this->bdoOportunidade = $bdoOportunidade;
        $this->bdoOportunidadeRepository = $bdoOportunidadeRepository;
        $this->bdoOportunidadeVariaveis = $bdoOportunidade->variaveis();
        $this->bdoEmpresa = $bdoEmpresa;
        $this->bdoEmpresaRepository = $bdoEmpresaRepository;
        $this->regionalRepository = $regionalRepository;
    }
    
    public function index()
    {
        $this->autoriza($this->class, __FUNCTION__);

        $resultados = $this->bdoOportunidadeRepository->getToTable();
        $tabela = $this->bdoOportunidade->tabelaCompleta($resultados);
        $variaveis = (object) $this->bdoOportunidadeVariaveis;

        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function create($id)
    {       
        $this->autoriza($this->class, __FUNCTION__);

        $empresa = $this->bdoEmpresaRepository->getToOportunidade($id);

        $regioes = $this->regionalRepository->getToList();
        
        if (isset($empresa)) {
            $variaveis = (object) $this->bdoOportunidadeVariaveis;
            $status = $this->bdoOportunidade::status();
            $segmentos = $this->bdoEmpresa::segmentos();

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
        $regioes = $this->regionalRepository->getToList();
        $regioesEdit = explode(',', $resultado->regiaoatuacao);
        $variaveis = (object) $this->bdoOportunidadeVariaveis;
        $status = $this->bdoOportunidade::status();
        $segmentos = $this->bdoEmpresa::segmentos();

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
        $tabela = $this->bdoOportunidade->tabelaCompleta($resultados);

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }
}