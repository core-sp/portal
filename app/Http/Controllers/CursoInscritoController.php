<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Curso;
use App\CursoInscrito;

class CursoInscritoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'inscricaoView', 'inscricao']);
    }

    public function create(Request $request, $idcurso)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $curso = Curso::find($idcurso);
        return view('admin.cursos.adicionar-inscrito', compact('curso'));
    }

    public function store(Request $request)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $idcurso = $request->input('idcurso');
        $regras = [
            'cpf' => 'required|unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso,
            'nome' => 'required',
            'telefone' => 'required|numeric',
            'email' => 'email'
        ];
        $mensagens = [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
            'numeric' => 'O :attribute aceita apenas números'
        ];
        $erros = $request->validate($regras, $mensagens);

        $inscrito = new CursoInscrito();
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $request->input('email');
        $inscrito->idcurso = $idcurso;
        $inscrito->save();
        return redirect()->route('cursos.lista');
    }

    public function cancelarInscricao(Request $request, $idcursoinscrito)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $inscrito = CursoInscrito::find($idcursoinscrito);
        $inscrito->idcurso = null;
        $inscrito->update();
        return redirect()->route('cursos.lista');
    }

    public function inscricaoView($idcurso)
    {
        $curso = Curso::find($idcurso);
        if ($curso) 
            return view('site.curso-inscricao', compact('curso'));
        else
            abort(404);
    }

    public function inscricao(Request $request)
    {
        $idcurso = $request->input('idcurso');
        $regras = [
            'cpf' => 'required|unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso,
            'nome' => 'required',
            'telefone' => 'required|numeric',
            'email' => 'email'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'numeric' => 'O :attribute aceita apenas números'
        ];
        $erros = $request->validate($regras, $mensagens);

        $inscrito = new CursoInscrito();
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $request->input('email');
        $inscrito->idcurso = $idcurso;
        $inscrito->save();

        $agradece = 'Sua inscrição em '.$inscrito->curso->tipo.' '.$inscrito->curso->tema.' foi efetuada com sucesso.';
        return view('site.agradecimento', compact('agradece'));
    }
}
