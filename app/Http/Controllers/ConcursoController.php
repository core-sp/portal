<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Concurso;

class ConcursoController extends Controller
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
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $concursos = Concurso::paginate(10);
        return view('admin.concursos.home', compact('concursos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        return view('admin.concursos.criar');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $regras = [
            'modalidade' => 'required',
            'nrprocesso' => 'required|unique:concursos',
            'situacao' => 'required',
            'datarealizacao' => 'required',
            'objeto' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'nrprocesso.unique' => 'Já existe um concurso com este nº de processo',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
        ];
        $erros = $request->validate($regras, $mensagens);

        $concurso = new Concurso();
        $concurso->modalidade = $request->input('modalidade');
        $concurso->nrprocesso = $request->input('nrprocesso');
        $concurso->situacao = $request->input('situacao');
        $concurso->datarealizacao = $request->input('datarealizacao');
        $concurso->objeto = $request->input('objeto');
        $concurso->idusuario = $request->input('idusuario');
        $concurso->save();
        return redirect()->route('concursos.lista');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $concurso = Concurso::find($id);
        return view('site.concurso', compact('concurso'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $concurso = Concurso::find($id);
        return view('admin.concursos.editar', compact('concurso'));
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
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $regras = [
            'modalidade' => 'required',
            'nrprocesso' => 'required',
            'situacao' => 'required',
            'datarealizacao' => 'required',
            'objeto' => 'required',
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'nrprocesso.required' => 'O nº do processo é obrigatório',
            'datarealizacao.required' => 'Informe a data de realização da Licitação',
        ];
        $erros = $request->validate($regras, $mensagens);

        $concurso = Concurso::find($id);
        $concurso->modalidade = $request->input('modalidade');
        $concurso->nrprocesso = $request->input('nrprocesso');
        $concurso->situacao = $request->input('situacao');
        $concurso->datarealizacao = $request->input('datarealizacao');
        $concurso->objeto = $request->input('objeto');
        $concurso->idusuario = $request->input('idusuario');
        $concurso->update();
        return redirect()->route('concursos.lista');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $concurso = Concurso::find($id);
        $concurso->delete();
        return redirect()->route('concursos.lista');
    }

    /**
     * Mostra a lixeira de concursos
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $concursos = Concurso::onlyTrashed()->get();
        return view('admin.concursos.lixeira', compact('concursos'));
    }

    /**
     * Restaura licitação deletada
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'juridico']);
        $concurso = Concurso::onlyTrashed()->find($id);
        $concurso->restore();
        return redirect()->route('concursos.lista');
    }
}
