<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoOportunidade;
use App\BdoEmpresa;

class BdoOportunidadeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $oportunidades = BdoOportunidade::paginate(10);
        return view('admin.bdo.home', compact('oportunidades')); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $id = Input::get('empresa');
        $empresa = BdoEmpresa::find($id);
        if (isset($empresa))
            return view('admin.bdo.criar', compact('empresa'));
        else
            abort(401);        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin']);
        $regras = [
            'vagasdisponiveis' => 'required',
            'descricao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $oportunidade = new BdoOportunidade();
        $oportunidade->idempresa = $request->input('empresa');
        $oportunidade->segmento = $request->input('segmento');
        $oportunidade->descricao = $request->input('descricao');
        $oportunidade->vagasdisponiveis = $request->input('vagasdisponiveis');
        $oportunidade->vagaspreenchidas = $request->input('vagaspreenchidas');
        $oportunidade->status = $request->input('status');
        if ($request->input('status') === "Em andamento") {
            $oportunidade->datainicio = now();
        } else {
            $oportunidade->datainicio = null;
        }
        $oportunidade->idusuario = $request->input('idusuario');
        $oportunidade->save();
        return redirect()->route('bdooportunidades.lista');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin']);
        $oportunidade = BdoOportunidade::find($id);
        return view('admin.bdo.editar', compact('oportunidade'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $regras = [
            'vagasdisponiveis' => 'required',
            'descricao' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $oportunidade = BdoOportunidade::find($id);
        $oportunidade->idempresa = $request->input('empresa');
        $oportunidade->segmento = $request->input('segmento');
        $oportunidade->descricao = $request->input('descricao');
        $oportunidade->vagasdisponiveis = $request->input('vagasdisponiveis');
        $oportunidade->vagaspreenchidas = $request->input('vagaspreenchidas');
        $oportunidade->status = $request->input('status');
        if ($oportunidade->datainicio === null && $request->input('status') === "Em andamento") {
            $oportunidade->datainicio = now();
        } else {
            $oportunidade->datainicio = null;
        }
        $oportunidade->idusuario = $request->input('idusuario');
        $oportunidade->update();
        return redirect()->route('bdooportunidades.lista');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function busca()
    {
        $busca = Input::get('q');
        $oportunidades = BdoOportunidade::where('descricao','LIKE','%'.$busca.'%')
            ->paginate(10);
        if (count($oportunidades) > 0) 
            return view('admin.bdo.home', compact('oportunidades', 'busca'));
        else
            return view('admin.bdo.home')->withMessage('Nenhum curso encontrado');
    }
}
