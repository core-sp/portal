<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Curso;
use App\CursoInscrito;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;
use App\Http\Controllers\ControleController;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\CursoInscritoMailGuest;

class CursoInscritoController extends Controller
{
    // Noma da classe pai (em relação à controle)
    private $class = 'CursoInscritoController';
    // Variáveis
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
            if(ControleController::mostra('CursoInscritoController', 'edit'))
                $acoes = '<a href="/admin/cursos/inscritos/editar/'.$resultado->idcursoinscrito.'" class="btn btn-sm btn-primary">Editar</a> ';
            else
                $acoes = '';
            if(ControleController::mostra('CursoInscritoController', 'destroy')) {
                $acoes .= '<form method="POST" action="/admin/cursos/cancelar-inscricao/'.$resultado->idcursoinscrito.'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar Inscrição" onclick="return confirm(\'Tem certeza que deseja cancelar a inscrição?\')" />';
                $acoes .= '</form>';
            }
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
            'table-hover'
        ];
        // Monta e retorna tabela        
        $tabela = CrudController::montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function create($idcurso)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $curso = Curso::find($idcurso);
        if(!$curso)
            abort(500);
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
        ControleController::autoriza($this->class, 'create');
        $idcurso = $request->input('idcurso');
        $regras = [
            'cpf' => 'required|max:191|unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso.',deleted_at,NULL',
            'nome' => 'required|max:191',
            'telefone' => 'required|max:191',
            'email' => 'email|max:191',
            'registrocore' => 'max:191'
        ];
        $mensagens = [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);

        $inscrito = new CursoInscrito();
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $request->input('email');
        $inscrito->registrocore = $request->input('registrocore');
        $inscrito->idusuario = $request->input('idusuario');
        $inscrito->idcurso = $idcurso;
        $save = $inscrito->save();
        if(!$save)
            abort(500);
        event(new CrudEvent('inscrito em curso', 'adicionou', $idcurso));
        return Redirect::route('inscritos.lista', array('id' => $idcurso))
            ->with('message', '<i class="icon fa fa-check"></i>Participante inscrito com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
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
        ControleController::autoriza($this->class, 'edit');
        $idcurso = $request->input('idcurso');
        $regras = [
            'cpf' => 'required|max:191|unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso.',deleted_at,NULL',
            'nome' => 'required|max:191',
            'telefone' => 'required|max:191',
            'email' => 'email|max:191',
            'registrocore' => 'max:191'
        ];
        $mensagens = [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);

        $inscrito = CursoInscrito::find($id);
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $request->input('email');
        $inscrito->registrocore = $request->input('registrocore');
        $inscrito->idusuario = $request->input('idusuario');
        $inscrito->idcurso = $idcurso;
        $update = $inscrito->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('inscrito em curso', 'editou', $inscrito->idcurso));
        return Redirect::route('inscritos.lista', array('id' => $idcurso))
            ->with('message', '<i class="icon fa fa-check"></i>Participante editado com sucesso!')
            ->with('class', 'alert-success');
    }

    public static function permiteInscricao($idcurso)
    {
        $contaInscritos = CursoInscrito::where('idcurso', $idcurso)->count();
        $curso = Curso::select('nrvagas')->find($idcurso);
        if(isset($curso)) {
            if ($contaInscritos < $curso->nrvagas) 
                return true;
            else
                return false;
        }
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
            'cpf' => 'required|max:191|unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso.',deleted_at,NULL',
            'nome' => 'required|max:191',
            'telefone' => 'required|max:191',
            'email' => 'email|max:191',
            'registrocore' => 'max:191'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'email' => 'Por favor, digite um endereço de email válido',
            'cpf.unique' => 'O CPF informado já está cadastrado neste curso',
            'max' => 'O :attribute excedeu o limite de caracteres permitido'
        ];
        $erros = $request->validate($regras, $mensagens);
        $emailUser = $request->input('email');
        // Inputa dados no Banco de Dados
        $inscrito = new CursoInscrito();
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $request->input('nome');
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $emailUser;
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
        $agradece .= "<strong>Detalhes da inscrição</strong><br>";
        $agradece .= "Nome: ".$inscrito->nome."<br>";
        $agradece .= "CPF: ".$inscrito->cpf."<br>";
        $agradece .= "Telefone: ".$inscrito->telefone;
        $agradece .= "<br><br>";
        $agradece .= "<strong>Detalhes do curso</strong><br>";
        $agradece .= "Nome: ".$inscrito->curso->tipo." - ".$inscrito->curso->tema."<br>";
        $agradece .= "Nº da turma: ".$inscrito->curso->idcurso."<br>";
        $agradece .= "Endereço: ".$inscrito->curso->endereco."<br>";
        $agradece .= "Data de Início: ".Helper::onlyDate($inscrito->curso->datarealizacao)."<br>";
        $agradece .= "Horário: ".Helper::onlyHour($inscrito->curso->datarealizacao)."h<br>";
        $adendo = '<i>* As informações foram enviadas ao email cadastrado no formulário</i>';
        Mail::to($emailUser)->queue(new CursoInscritoMailGuest($agradece));

        // Retorna view de agradecimento
        return view('site.agradecimento')->with([
            'agradece' => $agradece,
            'adendo' => $adendo
        ]);
    }

    public static function btnSituacao($idcurso)
    {
        $now = now();
        $curso = Curso::select('datarealizacao')->find($idcurso);
        if($curso->datarealizacao < $now) {
            echo "<div class='sit-btn sit-vermelho'>Já realizado</div>";
        } else {
            if(CursoInscritoController::permiteInscricao($idcurso)) {
                echo "<div class='sit-btn sit-verde'>Vagas Abertas</div>";
            } else {
                echo "<div class='sit-btn sit-vermelho'>Esgotado</div>";
            }
        }
    }

    public function destroy($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $curso = CursoInscrito::find($id);
        $delete = $curso->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('inscrito em curso', 'cancelou inscrição', $curso->idcurso));
        return Redirect::route('inscritos.lista', array('id' => $curso->idcurso))
            ->with('message', '<i class="icon fa fa-ban"></i>Inscrição cancelada com sucesso!')
            ->with('class', 'alert-danger');;
    }

}
