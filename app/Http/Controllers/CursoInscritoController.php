<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Curso;
use App\CursoInscrito;
use App\Http\Controllers\Helper;

class CursoInscritoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['inscricao', 'inscricaoView']]);
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
        $inscrito->registrocore = $request->input('registrocore');
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

    public static function permiteInscricao($idcurso)
    {
        $contaInscritos = CursoInscrito::where('idcurso', $idcurso)->count();
        $curso = Curso::find($idcurso);
        $vagas = $curso->nrvagas;
        if ($contaInscritos < $vagas) 
            return true;
        else
            return false;        
    }

    public function inscricaoView($idcurso)
    {
        if ($this->permiteInscricao($idcurso)) {
            $curso = Curso::find($idcurso);
            if ($curso) 
                return view('site.curso-inscricao', compact('curso'));
            else
                abort(403);
        } else {
            abort(403);
        }
    }

    public function inscricao(Request $request)
    {
        $idcurso = $request->input('idcurso');
        $regras = [
            'cpf' => 'required|unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso,
            'nome' => 'required',
            'telefone' => 'required',
            'email' => 'email'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'email' => 'Por favor, digite um endereço de email válido',
            'cpf.unique' => 'O CPF informado já está cadastrado neste curso'
        ];
        $erros = $request->validate($regras, $mensagens);

        $inscrito = new CursoInscrito();
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $request->input('email');
        $inscrito->registrocore = $request->input('registrocore');
        $inscrito->idcurso = $idcurso;
        $inscrito->save();

        $agradece = "Sua inscrição em <strong>".$inscrito->curso->tipo;
        $agradece .= " - ".$inscrito->curso->tema."</strong>";
        $agradece .= " (turma ".$inscrito->curso->idcurso.") foi efetuada com sucesso.";
        $agradece .= "<br><br>";
        $agradece .= "<strong>Endereço: </strong>".$inscrito->curso->endereco;
        $agradece .= "<br><strong>Data de Início: </strong>".Helper::onlyDate($inscrito->curso->datarealizacao);
        $agradece .= "<br><strong>Horário: </strong>".Helper::onlyHour($inscrito->curso->datarealizacao);

        return view('site.agradecimento')->with('agradece', $agradece);
    }

    public static function btnSituacao($idcurso)
    {
        if(CursoInscritoController::permiteInscricao($idcurso)) {
            echo "<div class='sit-btn sit-verde'>Vagas Abertas</div>";
        } else {
            echo "<div class='sit-btn sit-vermelho'>Esgotado</div>";
        }
    }
}
