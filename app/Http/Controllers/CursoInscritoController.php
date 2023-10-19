<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Curso;
use App\CursoInscrito;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CrudController;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\CursoInscritoMailGuest;
use App\Rules\Cpf;
use Illuminate\Support\Facades\Input;
use App\Events\ExternoEvent;
use Response;
use Auth;
use Illuminate\Support\Facades\Request as IlluminateRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Repositories\GerentiRepositoryInterface;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CursoInscricaoRequest;

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
    private $gerentiRepository;
    private $service;
    
    public function __construct(GerentiRepositoryInterface $gerentiRepository, MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['inscricao', 'inscricaoView']]);
        $this->gerentiRepository = $gerentiRepository;
        $this->service = $service;
    }

    public function index($idcurso)
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $curso = $this->service->getService('Curso')->show($idcurso);
            $dados = $this->service->getService('Curso')->inscritos()->listar($curso, auth()->user());
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar as inscrições do curso com ID ".$idcurso.".");
        }

        return view('admin.crud.home', $dados);
    }

    public function create($idcurso)
    {
        $this->authorize('create', auth()->user());
        
        try{
            $curso = $this->service->getService('Curso')->show($idcurso);
            $dados = $this->service->getService('Curso')->inscritos()->view($curso);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar a página para adicionar um inscrito no curso com ID ".$idcurso.".");
        }

        return view('admin.crud.criar', $dados);
    }

    // inscrição via área admin
    public function store(CursoInscricaoRequest $request, $idcurso)
    {
        $this->authorize('create', auth()->user());
        
        try{
            $validated = $request->validated();
            $curso = $this->service->getService('Curso')->show($idcurso);
            $dados = $this->service->getService('Curso')->inscritos()->save($validated, auth()->user(), $curso);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, "Curso não encontrado.");
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao adicionar um inscrito no curso com ID ".$idcurso.".");
        }

        return redirect()->route('inscritos.index', $idcurso)
            ->with('message', '<i class="icon fa fa-check"></i>Participante inscrito com sucesso!')
            ->with('class', 'alert-success');
    }

    public function edit($id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $dados = $this->service->getService('Curso')->inscritos()->view(null, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao carregar a página para editar um inscrito com ID ".$id.".");
        }

        return view('admin.crud.editar', $dados);
    }

    // atualizar inscrição via área admin
    public function update(CursoInscricaoRequest $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
        
        try{
            $validated = $request->validated();
            $dados = $this->service->getService('Curso')->inscritos()->save($validated, auth()->user(), null, $id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao editar um inscrito no curso com ID ".$dados['idcurso'].".");
        }

        return redirect()->route('inscritos.index', $dados['idcurso'])
            ->with('message', '<i class="icon fa fa-check"></i>Participante com ID '.$id.' foi editado com sucesso!')
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
            $rep = auth()->guard('representante')->check();
            $user_rep = $rep ? auth()->guard('representante')->user() : null;
            $situacao = $rep ? trim($this->gerentiRepository->gerentiStatus($user_rep->ass_id)) : '';

            if($rep)
            {
                $tel = $user_rep->getContatosTipoTelefone($this->gerentiRepository);
                $user_rep->telefone = empty($tel) ? '' : $tel[array_keys($tel)[0]]['CXP_VALOR'];
                $user_rep->registro_core = $this->gerentiRepository->gerentiAtivo(apenasNumeros($user_rep->cpf_cnpj))[0]['REGISTRONUM'];
            }

            if ($curso && $curso->liberarAcesso($rep, $situacao)) 
                return view('site.curso-inscricao', compact('curso', 'user_rep'));
            else
                return $situacao == '' ? redirect()->route('representante.login')->with([
                    'message' => 'Deve realizar login na área restrita do representante para se inscrever.',
                    'class' => 'alert-danger'
                ]) : redirect()->route('representante.cursos')->with([
                    'message' => '<i class="fas fa-info-circle"></i>&nbsp;Para liberar sua inscrição entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.',
                    'class' => 'alert-danger'
                ]);
        } else {
            abort(500);
        }
    }

    // inscrição via área aberta
    public function inscricao(Request $request)
    {
        $idcurso = $request->input('idcurso');

        $curso = Curso::findOrFail($idcurso);
        $rep = auth()->guard('representante')->check();
        $user_rep = $rep ? auth()->guard('representante')->user() : null;
        $situacao = $rep ? trim($this->gerentiRepository->gerentiStatus($user_rep->ass_id)) : '';

        if(!$curso->liberarAcesso($rep, $situacao))
            return $situacao == '' ? redirect()->route('representante.login')->with([
                'message' => 'Deve realizar login na área restrita do representante para se inscrever.',
                'class' => 'alert-danger'
            ]) : redirect()->route('representante.cursos')->with([
                'message' => '<i class="fas fa-info-circle"></i>&nbsp;Para liberar sua inscrição entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.',
                'class' => 'alert-danger'
            ]);

        if($rep)
            request()->merge([
                'cpf' => $user_rep->cpf_cnpj,
                'nome' => $user_rep->nome,
                'email' => $user_rep->email,
            ]);

        $unique = in_array($idcurso, ['43', '44', '45']) ? null : 'unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso.',deleted_at,NULL';
        $regras = $rep ? [
            'cpf' => 'required|' . $unique,
            'termo' => 'required|accepted'
            ] : [
            'cpf' => ['required', 'max:191', new Cpf, $unique/*'unique:curso_inscritos,cpf,NULL,idcurso,idcurso,'.$idcurso.',deleted_at,NULL'*/],
            'nome' => 'required|max:191|regex:/^[a-zA-Z ÁáÉéÍíÓóÚúÃãÕõÂâÊêÔô]+$/',
            'telefone' => 'required|max:191|min:14',
            'email' => 'email|max:191',
            'registrocore' => 'max:191',
            'termo' => 'sometimes|required|accepted'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'email' => 'Email inválido',
            'nome.regex' => 'Nome inválido',
            'cpf.unique' => $rep && ($user_rep->tipoPessoa() == 'PJ') ? 'O CNPJ informado já está cadastrado neste curso' : 'O CPF informado já está cadastrado neste curso',
            'max' => 'O :attribute excedeu o limite de caracteres permitido',
            'min' => 'O :attribute deve ter :min ou mais caracteres',
            'telefone.min' => 'Telefone inválido',
            'accepted' => 'Você deve concordar com o Termo de Consentimento',
        ];
        $validation = Validator::make($request->all(), $regras, $mensagens);
        if($validation->fails()) {
            return Redirect::back()->withErrors($validation)->withInput($request->all());
        }
        if(!$this->permiteInscricao($idcurso))
            abort(500, 'As inscrições para este curso estão esgotadas!');

        if($rep)
        {
            $tel = $user_rep->getContatosTipoTelefone($this->gerentiRepository);
            $temp_tel = empty($tel) ? '' : $tel[array_keys($tel)[0]]['CXP_VALOR'];
            $temp_reg = $this->gerentiRepository->gerentiAtivo(apenasNumeros($user_rep->cpf_cnpj))[0]['REGISTRONUM'];
            request()->merge([
                'telefone' => $temp_tel,
                'registrocore' => $temp_reg,
            ]);
        }

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

        $termo = $inscrito->termos()->create([
            'ip' => request()->ip()
        ]);

        // Gera evento de inscrição no Curso
        $string = $inscrito->nome." (CPF: ".$inscrito->cpf.")";
        $string .= " *inscreveu-se* no curso *".$inscrito->curso->tipo." - ".$inscrito->curso->tema;
        $string .= "*, turma *".$inscrito->curso->idcurso."*";
        $string .= " e " . $termo->message();

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
        $this->authorize('delete', auth()->user());
        
        try{
            $dados = $this->service->getService('Curso')->inscritos()->destroy($id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [403]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, "Erro ao cancelar a inscrição com ID ".$id.".");
        }

        return redirect()->route('inscritos.index', $dados['idcurso'])
            ->with('message', '<i class="icon fa fa-check"></i>Inscrição com ID '.$id.' foi cancelada com sucesso!')
            ->with('class', 'alert-success');
    }

    public function busca(Request $request, $id)
    {
        $this->authorize('viewAny', auth()->user());
        
        try{
            $busca = $request->q;
            $curso = $this->service->getService('Curso')->show($id);
            $dados = $this->service->getService('Curso')->inscritos()->buscar($curso, $busca, auth()->user());
            $dados['busca'] = $busca;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao buscar o texto em inscritos no curso com ID ".$id.".");
        }

        return view('admin.crud.home', $dados);
    }

    public function download($id)
    {
        $this->authorize('viewAny', auth()->user());
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=inscritos-'.$id.'.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];
        $resultado = CursoInscrito::select('email','cpf','nome','telefone','registrocore','tipo_inscrito','created_at')
            ->where('idcurso', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        $lista = $resultado->toArray();
        array_unshift($lista, array_keys($lista[0]));
        $callback = function() use($lista) {
            $fh = fopen('php://output','w');
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach($lista as $linha) {
                fputcsv($fh,$linha,';');
            }
            fclose($fh);
        };
        return Response::stream($callback, 200, $headers);
    }

    public function confirmarPresenca(Request $request, $id)
    {
        $this->authorize('updateOther', auth()->user());
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
        $this->authorize('updateOther', auth()->user());
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
