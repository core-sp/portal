<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Curso;
use App\Regional;
use App\CursoInscrito;

class CursoController extends Controller
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
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $cursos = Curso::paginate(10);
        return view('admin.cursos.home', compact('cursos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regionais = Regional::all();
        return view('admin.cursos.criar', compact('regionais'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regras = [
            'tema' => 'required',
            'datarealizacao' => 'required',
            'duracao' => 'required',
            'endereco' => 'required',
            'nrvagas' => 'required|numeric',
            'descricao' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'numeric' => 'O :attribute aceita apenas números'
        ];
        $erros = $request->validate($regras, $mensagens);

        $curso = New Curso();
        $curso->tipo = $request->input('tipo');
        $curso->tema = $request->input('tema');
        $curso->datarealizacao = $request->input('datarealizacao');
        $curso->duracao = $request->input('duracao');
        $curso->endereco = $request->input('endereco');
        $curso->nrvagas = $request->input('nrvagas');
        $curso->idregional = $request->input('idregional');
        $curso->descricao = $request->input('descricao');
        $curso->observacao = $request->input('observacao');
        $curso->idusuario = $request->input('idusuario');
        $curso->save();
        return redirect()->route('cursos.lista');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $curso = Curso::find($id);
        return view('site.curso', compact('curso'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $curso = Curso::find($id);
        $regionais = Regional::all();
        return view('admin.cursos.editar', compact('curso', 'regionais'));
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
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $regras = [
            'tema' => 'required',
            'datarealizacao' => 'required',
            'duracao' => 'required',
            'endereco' => 'required',
            'nrvagas' => 'required|numeric',
            'descricao' => 'required'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'numeric' => 'O :attribute aceita apenas números'
        ];
        $erros = $request->validate($regras, $mensagens);

        $curso = Curso::find($id);
        $curso->tipo = $request->input('tipo');
        $curso->tema = $request->input('tema');
        $curso->datarealizacao = $request->input('datarealizacao');
        $curso->duracao = $request->input('duracao');
        $curso->endereco = $request->input('endereco');
        $curso->nrvagas = $request->input('nrvagas');
        $curso->idregional = $request->input('idregional');
        $curso->descricao = $request->input('descricao');
        $curso->observacao = $request->input('observacao');
        $curso->idusuario = $request->input('idusuario');
        $curso->update();
        return redirect()->route('cursos.lista');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $curso = Curso::find($id);
        $curso->delete();
        return redirect()->route('cursos.lista');
    }

    /**
     * Mostra a lixeira de cursos
     *
     * @return \Illuminate\Http\Response
     */
    public function lixeira(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $cursos = Curso::onlyTrashed()->get();
        return view('admin.cursos.lixeira', compact('cursos'));
    }

    /**
     * Restaura licitação deletada
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $curso = Curso::onlyTrashed()->find($id);
        $curso->restore();
        return redirect()->route('cursos.lista');
    }

    public function inscritos(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $inscritos = CursoInscrito::where('idcurso', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $curso = Curso::find($id);
        return view('admin.cursos.inscritos', compact('inscritos', 'curso'));
    }
}
