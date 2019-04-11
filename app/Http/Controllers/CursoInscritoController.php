<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Curso;
use App\CursoInscrito;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;

class CursoInscritoController extends Controller
{
    public $variaveis = [
        'singular' => 'inscrito',
        'singulariza' => 'o inscrito',
        'plural' => 'inscritos',
        'pluraliza' => 'inscritos'
    ];
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['inscricao', 'inscricaoView']]);
    }

    public static function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'CPF',
            'Nome',
            'Telefone',
            'Email',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '<a href="/admin/cursos/inscritos/editar/'.$resultado->idcursoinscrito.'" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="/admin/cursos/cancelar-inscricao/'.$resultado->idcursoinscrito.'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar Inscrição" onclick="return confirm(\'Tem certeza que deseja cancelar a inscrição?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->cpf,
                $resultado->nome,
                $resultado->telefone,
                $resultado->email,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hovered'
        ];
        // Monta e retorna tabela        
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function create(Request $request, $idcurso)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $curso = Curso::find($idcurso);
        $variaveis = [
            'form' => 'cursoinscrito',
            'singulariza' => 'o inscrito',
            'titulo_criar' => 'Adicionar inscrito em '.$curso->tipo.': '.$curso->tema,
        ];
        $variaveis = (object) $variaveis;
        return view('admin.crud.criar', compact('curso', 'variaveis'));
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
        $save = $inscrito->save();
        if(!$save)
            abort(500);
        return Redirect::route('inscritos.lista', array('id' => $idcurso));
    }

    public function edit(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $resultado = CursoInscrito::find($id);
        $variaveis = [
            'form' => 'cursoinscrito',
            'singular' => 'inscrito',
            'singulariza' => 'o inscrito'
        ];
        $variaveis = (object) $variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function update(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $idcurso = $request->input('idcurso');
        $regras = [
            'cpf' => 'required',
            'nome' => 'required',
            'telefone' => 'required',
            'email' => 'email'
        ];
        $mensagens = [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
        ];
        $erros = $request->validate($regras, $mensagens);

        $inscrito = CursoInscrito::find($id);
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $request->input('email');
        $inscrito->registrocore = $request->input('registrocore');
        $inscrito->idcurso = $idcurso;
        $update = $inscrito->update();
        if(!$update)
            abort(500);
        return Redirect::route('inscritos.lista', array('id' => $idcurso));
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
        // Inputa dados no Banco de Dados
        $inscrito = new CursoInscrito();
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $request->input('email');
        $inscrito->registrocore = $request->input('registrocore');
        $inscrito->idcurso = $idcurso;
        $save = $inscrito->save();
        if(!$save)
            abort(500);
        // Gera mensagem de agradecimento
        $agradece = "Sua inscrição em <strong>".$inscrito->curso->tipo;
        $agradece .= " - ".$inscrito->curso->tema."</strong>";
        $agradece .= " (turma ".$inscrito->curso->idcurso.") foi efetuada com sucesso.";
        $agradece .= "<br><br>";
        $agradece .= "<strong>Endereço: </strong>".$inscrito->curso->endereco;
        $agradece .= "<br><strong>Data de Início: </strong>".Helper::onlyDate($inscrito->curso->datarealizacao);
        $agradece .= "<br><strong>Horário: </strong>".Helper::onlyHour($inscrito->curso->datarealizacao);
        // Retorna view de agradecimento
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

    public function destroy(Request $request, $id)
    {
        $request->user()->autorizarPerfis(['admin', 'editor']);
        $curso = CursoInscrito::find($id);
        $delete = $curso->delete();
        if(!$delete)
            abort(500);
        return Redirect::route('inscritos.lista', array('id' => $curso->idcurso));
    }

}
