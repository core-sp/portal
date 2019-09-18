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
use App\Rules\Cpf;
use Illuminate\Support\Facades\Input;
use App\Events\ExternoEvent;
use Response;
use Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CursoInscritoController extends Controller
{
    // Noma da classe pai (em relação à controle)
    private $class = 'CursoInscritoController';
    // Variáveis
    public $variaveis = [
        'singular' => 'inscrito',
        'singulariza' => 'o inscrito',
        'plural' => 'inscritos',
        'pluraliza' => 'inscritos',
        'busca' => 'cursos/inscritos'
    ];
    
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['inscricao', 'inscricaoView']]);
    }

    public static function tabelaCompleta($resultados, $idcurso = null)
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
        $now = date('Y-m-d H:i:s');
        $curso = Curso::select('datatermino')->findOrFail($idcurso);
        foreach($resultados as $resultado) {
            $acoes = '';
            if($curso->datatermino >= $now) {
                if(ControleController::mostra('CursoInscritoController', 'destroy')) {
                    $acoes .= '<form method="POST" action="/admin/cursos/cancelar-inscricao/'.$resultado->idcursoinscrito.'" class="d-inline">';
                    $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                    $acoes .= '<input type="hidden" name="_method" value="delete" />';
                    $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar Inscrição" onclick="return confirm(\'Tem certeza que deseja cancelar a inscrição?\')" />';
                    $acoes .= '</form>';
                }
            } else {
                if($resultado->presenca === null) {
                    $acoes .= '<form method="POST" action="/admin/cursos/inscritos/confirmar-presenca/'.$resultado->idcursoinscrito.'" class="d-inline">';
                    $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                    $acoes .= '<input type="hidden" name="_method" value="put" />';
                    $acoes .= '<input type="submit" class="btn btn-sm btn-success" value="Confirmar presença" />';
                    $acoes .= '</form> ';
                    $acoes .= '<form method="POST" action="/admin/cursos/inscritos/confirmar-falta/'.$resultado->idcursoinscrito.'" class="d-inline">';
                    $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                    $acoes .= '<input type="hidden" name="_method" value="put" />';
                    $acoes .= '<input type="submit" class="btn btn-sm btn-warning" value="Dar falta" />';
                    $acoes .= '</form>';
                } elseif ($resultado->presenca === 'Sim') {
                    $acoes .= "<p class='d-inline text-success'><strong><i class='fas fa-check checkIcone'></i> Compareceu&nbsp;</strong></p>";
                } else {
                    $acoes .= "<p class='d-inline text-danger'><strong><i class='fas fa-ban checkIcone'></i> Não Compareceu&nbsp;</strong></p>";
                }
            }
            if(ControleController::mostra('CursoInscritoController', 'edit'))
                $acoes .= ' <a href="/admin/cursos/inscritos/editar/'.$resultado->idcursoinscrito.'" class="btn btn-sm btn-default">Editar</a> ';
            else
                $acoes .= '';
            if(empty($acoes))
                $acoes = '<i class="fas fa-lock text-muted"></i>';
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
        $now = date('Y-m-d H:i:s');
        $curso = Curso::findOrFail($idcurso);
        if(!$curso)
            abort(500);
        if($curso->datatermino <= $now)
            abort(401);
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
        $regras = [
            'cpf' => ['required', 'max:191', 'unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$request->input('idcurso').',deleted_at,NULL', new Cpf],
            'nome' => 'required|max:191',
            'telefone' => 'required|max:191|min:14',
            'email' => 'required|email|max:191',
            'registrocore' => 'max:191'
        ];
        $mensagens = [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'telefone.min' => 'Telefone inválido',
            'email' => 'Digite um email válido'
        ];
        $erros = $request->validate($regras, $mensagens);

        $nomeUser = mb_convert_case(mb_strtolower(request('nome')), MB_CASE_TITLE);

        $save = CursoInscrito::create([
            'cpf' => request('cpf'),
            'nome' => $nomeUser,
            'telefone' => request('telefone'),
            'email' => request('email'),
            'registrocore' => request('registrocore'),
            'idcurso' => request('idcurso'),
            'idusuario' => request('idusuario')
        ]);

        if(!$save)
            abort(500);
        event(new CrudEvent('inscrito em curso', 'adicionou', request('idcurso')));
        return Redirect::route('inscritos.lista', array('id' => request('idcurso')))
            ->with('message', '<i class="icon fa fa-check"></i>Participante inscrito com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $resultado = CursoInscrito::findOrFail($id);
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
            'cpf' => ['required', 'max:191', new Cpf, Rule::unique('curso_inscritos')->where(function ($q) use ($idcurso, $id) {
                return $q->where('idcurso', $idcurso)->where('idcursoinscrito','!=',$id);
            })],
            'nome' => 'required|max:191',
            'telefone' => 'required|max:191',
            'email' => 'required|email|max:191|min:14',
            'registrocore' => 'max:191'
        ];
        $mensagens = [
            'cpf.unique' => 'Este CPF já está cadastrado para o curso',
            'required' => 'O :attribute é obrigatório',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'telefone.min' => 'Telefone inválido',
            'email' => 'Digite um email válido'
        ];
        $erros = $request->validate($regras, $mensagens);
        $nomeUser = mb_convert_case(mb_strtolower(request('nome')), MB_CASE_TITLE);

        $inscrito = CursoInscrito::findOrFail($id);
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $nomeUser;
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
        $curso = Curso::select('nrvagas')->findOrFail($idcurso);
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
            $curso = Curso::findOrFail($idcurso);
            if ($curso) 
                return view('site.curso-inscricao', compact('curso'));
            else
                abort(500);
        } else {
            abort(500);
        }
    }

    public function inscricao(Request $request)
    {
        $idcurso = $request->input('idcurso');
        $regras = [
            'cpf' => ['required', 'max:191', 'unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso.',deleted_at,NULL', new Cpf],
            'nome' => 'required|max:191',
            'telefone' => 'required|max:191|min:14',
            'email' => 'email|max:191',
            'registrocore' => 'max:191'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'email' => 'Email inválido',
            'cpf.unique' => 'O CPF informado já está cadastrado neste curso',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'telefone.min' => 'Telefone inválido'
        ];
        $validation = Validator::make($request->all(), $regras, $mensagens);
        if($validation->fails()) {
            return Redirect::back()->withErrors($validation)->withInput($request->all());
        }
        if(!$this->permiteInscricao($idcurso))
            abort(500, 'As inscrições para este curso estão esgotadas!');
        $emailUser = $request->input('email');
        $nomeUser = mb_convert_case(mb_strtolower(request('nome')), MB_CASE_TITLE);
        // Inputa dados no Banco de Dados
        $inscrito = new CursoInscrito();
        $inscrito->cpf = $request->input('cpf');
        $inscrito->nome = $nomeUser;
        $inscrito->telefone = $request->input('telefone');
        $inscrito->email = $emailUser;
        $inscrito->registrocore = $request->input('registrocore');
        $inscrito->idcurso = $idcurso;
        $save = $inscrito->save();
        if(!$save)
            abort(500);
        // Gera evento de inscrição no Curso
        $string = $inscrito->nome." (CPF: ".$inscrito->cpf.")";
        $string .= " *inscreveu-se* no curso *".$inscrito->curso->tipo." - ".$inscrito->curso->tema;
        $string .= "*, turma *".$inscrito->curso->idcurso."*";
        event(new ExternoEvent($string));
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
        $now = date('Y-m-d H:i:s');
        $curso = Curso::select('datatermino')->findOrFail($idcurso);
        if($curso->datatermino <= $now) {
            echo "<div class='sit-btn sit-vermelho'>Já realizado</div>";
        } else {
            if(CursoInscritoController::permiteInscricao($idcurso)) {
                echo "<div class='sit-btn sit-verde'>Vagas Abertas</div>";
            } else {
                echo "<div class='sit-btn sit-vermelho'>Vagas esgotadas</div>";
            }
        }
    }

    public function destroy($id)
    {
        ControleController::autoriza($this->class, __FUNCTION__);
        $curso = CursoInscrito::findOrFail($id);
        $now = date('Y-m-d H:i:s');
        if($curso->datatermino >= $now)
            abort(401);
        $delete = $curso->delete();
        if(!$delete)
            abort(500);
        event(new CrudEvent('inscrito em curso', 'cancelou inscrição', $curso->idcurso));
        return Redirect::route('inscritos.lista', array('id' => $curso->idcurso))
            ->with('message', '<i class="icon fa fa-ban"></i>Inscrição cancelada com sucesso!')
            ->with('class', 'alert-danger');;
    }

    public function busca($id)
    {
        ControleController::autoriza('CursoInscritoController', 'index');
        $busca = Input::get('q');
        $curso = Curso::findOrFail($id);
        $now = date('Y-m-d H:i:s');
        $resultados = CursoInscrito::where('idcurso',$id)
            ->where(function($query) use($busca){
                $query->where('cpf','LIKE','%'.$busca.'%')
                ->orWhere('nome','LIKE','%'.$busca.'%')
                ->orWhere('email','LIKE','%'.$busca.'%');
            })->paginate(10);
        $this->variaveis['continuacao_titulo'] = 'em '.$curso->tipo.': '.$curso->tema;
        if($curso->datatermino >= $now) 
            $this->variaveis['btn_criar'] = '<a href="/admin/cursos/adicionar-inscrito/'.$curso->idcurso.'" class="btn btn-primary mr-1">Adicionar inscrito</a> ';
        $this->variaveis['btn_lixeira'] = '<a href="/admin/cursos" class="btn btn-default">Lista de Cursos</a>';
        $this->variaveis['busca'] = 'cursos/inscritos/'.$id;
        $this->variaveis['slug'] = 'cursos/inscritos/'.$id;
        $variaveis = (object) $this->variaveis;
        $tabela = $this->tabelaCompleta($resultados, $id);
        return view('admin.crud.home', compact('resultados', 'busca', 'tabela', 'variaveis'));
    }

    public function download($id)
    {
        ControleController::autoriza($this->class, 'index');
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=inscritos-'.$id.'.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];
        $resultado = CursoInscrito::select('cpf','nome','telefone','email','registrocore','created_at')
            ->where('idcurso', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        $lista = $resultado->toArray();
        array_unshift($lista, array_keys($lista[0]));
        $callback = function() use($lista) {
            $fh = fopen('php://output','w');
            foreach($lista as $linha) {
                fputcsv($fh,$linha);
            }
            fclose($fh);
        };
        return Response::stream($callback, 200, $headers);
    }

    public function confirmarPresenca(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $idusuario = Auth::user()->idusuario;
        $inscrito = CursoInscrito::findOrFail($id);
        $inscrito->presenca = 'Sim';
        $update = $inscrito->update();
        if(!$update)
            abort(500);
            event(new CrudEvent('no curso', 'confirmou presença do participante '.$id, $inscrito->idcurso));
        return Redirect::back()
            ->with('message', '<i class="icon fa fa-check"></i>Presença confirmada!')
            ->with('class', 'alert-success');
    }

    public function confirmarFalta(Request $request, $id)
    {
        ControleController::autoriza($this->class, 'edit');
        $idusuario = Auth::user()->idusuario;
        $inscrito = CursoInscrito::findOrFail($id);
        $inscrito->presenca = 'Não';
        $update = $inscrito->update();
        if(!$update)
            abort(500);
        event(new CrudEvent('no curso', 'confirmou falta do participante '.$id, $inscrito->idcurso));
        return Redirect::back()
            ->with('message', '<i class="icon fa fa-ban"></i>Falta confirmada!')
            ->with('class', 'alert-warning');
    }

}
