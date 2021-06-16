<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Regional;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;
use App\Http\Requests\RegionalRequest;
use App\Repositories\RegionalRepository;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class RegionalController extends Controller
{
    private $class = 'RegionalController';
    private $regionalModel;
    private $regionalRepository;
    private $variaveis;

    public function __construct(Regional $regional, RegionalRepository $regionalRepository)
    {
        $this->middleware('auth', ['except' => ['show', 'siteGrid']]);
        $this->regionalModel = $regional;
        $this->regionalRepository = $regionalRepository;
        $this->variaveis = $regional->variaveis();
    }
    
    public function index()
    {
        $resultados = $this->regionalRepository->all();
        $tabela = $this->regionalModel->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.home', compact('tabela', 'variaveis', 'resultados'));
    }

    public function edit(Request $request, $id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = $this->regionalRepository->getById($id);
        $variaveis = (object) $this->variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(RegionalRequest $request, $id)
    {
        $request->validated();

        $update = $this->regionalRepository->update($id, $request);
        if(!$update)
            abort(500);
        
        event(new CrudEvent('regional', 'editou', $id));
        return redirect(route('regionais.index'))
            ->with('message', '<i class="icon fa fa-check"></i>Regional editada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function show($id)
    {
        $resultado = $this->regionalRepository->getById($id);
        $noticias = $this->regionalRepository->getRegionalNoticias($id);
        return response()
            ->view('site.regional', compact('resultado','noticias'))
            ->header('Cache-Control','no-cache');
    }

    public function busca()
    {
        $busca = IlluminateRequest::input('q');
        $resultados = $this->regionalRepository->getBusca($busca);
        $tabela = $this->regionalModel->tabelaCompleta($resultados);
        $variaveis = (object) $this->variaveis;

        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function siteGrid()
    {
        $regionais = $this->regionalRepository->all();
        return response()
            ->view('site.regionais', compact('regionais'))
            ->header('Cache-Control','no-cache');
    }
}