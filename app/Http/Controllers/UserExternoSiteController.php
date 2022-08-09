<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserExternoRequest;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PreRegistroAjaxRequest;
use App\Http\Requests\PreRegistroRequest;
use App\Repositories\GerentiRepositoryInterface;

class UserExternoSiteController extends Controller
{
    private $service;
    private $gerentiRepository;

    public function __construct(MediadorServiceInterface $service, GerentiRepositoryInterface $gerentiRepository)
    {        
        // Limitação de requisições por minuto para cada usuário, senão erro 429
        $qtd = '60';
        if(config('app.env') == "testing")
            $qtd = '100';

        $this->middleware(['auth:user_externo', 'throttle:' . $qtd . ',1'])->except(['cadastroView', 'cadastro', 'verificaEmail']);
        $this->service = $service;
        $this->gerentiRepository = $gerentiRepository;
    }

    public function cadastroView()
    {
        return view('site.userExterno.cadastro');
    }

    public function cadastro(UserExternoRequest $request)
    {
        try{
            $validated = $request->validated();
            $dados = $this->service->getService('UserExterno')->save($validated);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao criar o cadastro no Login Externo');
        }

        if(isset($dados['erro']))
            return redirect(route('externo.cadastro'))->withInput()->with([
                'message' => $dados['erro'],
                'class' => $dados['class']
            ]);

        return view('site.agradecimento')->with([
            'agradece' => 'Cadastro no Login Externo realizado com sucesso. Por favor, <strong>acesse o email informado para confirmar seu cadastro.</strong>'
        ]);
    }

    public function verificaEmail($token)
    {
        try{
            $erro = $this->service->getService('UserExterno')->verificaEmail($token);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao atualizar a verificação de email do cadastro no Login Externo');
        }
        
        $success = [
            'message' => 'Email verificado com sucesso. Favor continuar com o login abaixo.',
            'class' => 'alert-success'
        ];

        return redirect(route('externo.login'))->with(isset($erro['message']) ? $erro : $success);
    }

    public function index()
    {
        return view('site.userExterno.home');
    }

    public function editarView()
    {
        $resultado = auth()->guard('user_externo')->user();

        return view('site.userExterno.dados', compact('resultado'));
    }

    public function editarSenhaView()
    {
        $alterarSenha = true;
        return view('site.userExterno.dados', compact('alterarSenha'));
    }

    public function editar(UserExternoRequest $request)
    {
        try{
            $validate = $request->validated();
            $externo = auth()->guard('user_externo')->user();
            $erro = $this->service->getService('UserExterno')->editDados($validate, $externo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao atualizar os dados cadastrais no Login Externo');
        }

        return isset($erro['message']) ? redirect(route('externo.editar.senha.view'))->with($erro) : 
            redirect(route('externo.editar.view'))->with([
                'message' => 'Dados alterados com sucesso.',
                'class' => 'alert-success'
            ]);
    }
    
    public function preRegistroView()
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $dados = $this->service->getService('PreRegistro')->verificacao($this->gerentiRepository, $externo);
            $gerenti = $dados['gerenti'];
            $resultado = isset($gerenti) ? null : $externo->load('preRegistro')->preRegistro;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao verificar os dados para permitir ou não a solicitação de registro');
        }

        return view('site.userExterno.pre-registro', compact('resultado', 'gerenti'));
    }

    public function inserirPreRegistroView(Request $request)
    {
        try{
            if($request->checkPreRegistro != 'on')
                return redirect(route('externo.preregistro.view'));

            $externo = auth()->guard('user_externo')->user();
            $dados = $this->service->getService('PreRegistro')->verificacao($this->gerentiRepository, $externo);
            if(isset($dados['gerenti']))
                return view('site.userExterno.pre-registro', ['resultado' => null, 'gerenti' => $dados['gerenti']]);
                
            $dados = $this->service->getService('PreRegistro')->getPreRegistro($this->service, $externo);
            $codigos = $dados['codigos'];
            $resultado = $dados['resultado'];
            $regionais = $dados['regionais'];
            $classes = $dados['classes'];
            $totalFiles = $dados['totalFiles'];
            $abas = $dados['abas'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao carregar os dados da solicitação de registro');
        }

        return view('site.userExterno.inserir-pre-registro', compact('resultado', 'regionais', 'totalFiles', 'codigos', 'classes', 'abas'));
    }

    public function inserirPreRegistroAjax(PreRegistroAjaxRequest $request)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveSiteAjax($validatedData, $this->gerentiRepository, $externo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao salvar os dados da solicitação de registro via ajax');
        }
        
        return response()->json($dados);
    }

    public function verificaPendenciaPreRegistro(PreRegistroRequest $request)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $dados = $this->service->getService('PreRegistro')->getPreRegistro($this->service, $externo);
            $codigos = $dados['codigos'];
            $resultado = $dados['resultado'];

            if(!$resultado->userPodeEditar())
                throw new \Exception('Não autorizado a editar o formulário com a solicitação em análise ou finalizada', 401);

            $regionais = $dados['regionais'];
            $classes = $dados['classes'];
            $totalFiles = $dados['totalFiles'];
            $abas = $dados['abas'];
            $semPendencia = true;
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao verificar pendências da solicitação de registro');
        }
        
        return view('site.userExterno.inserir-pre-registro', compact('semPendencia', 'resultado', 'regionais', 'totalFiles', 'codigos', 'classes', 'abas'));
    }

    // Esse request não devolve a página para correção.
    // Apenas valida os dados já salvos no bd que foram carregados no form novamente ou via request direto
    // Apenas rota de confirmação do envio e onde é realizado os processos
    public function inserirPreRegistro(PreRegistroRequest $request)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveSite($validatedData, $this->gerentiRepository, $externo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao enviar os dados da solicitação de registro para análise');
        }
        
        return redirect(route('externo.preregistro.view'))->with($dados);
    }

    public function preRegistroAnexoDownload($id)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $preRegistro = $externo->load('preRegistro')->preRegistro;

            if(!isset($preRegistro))
                throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

            $file = $this->service->getService('PreRegistro')->downloadAnexo($id, $preRegistro->id);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao solicitar download do arquivo');
        }
        
        return $file;
    }

    public function preRegistroAnexoExcluir($id)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $dados = $this->service->getService('PreRegistro')->excluirAnexo($id, $externo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao solicitar exclusão do arquivo');
        }
        
        return response()->json($dados);
    }
}