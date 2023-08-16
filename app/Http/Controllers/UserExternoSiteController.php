<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserExternoRequest;
use App\Contracts\MediadorServiceInterface;
use App\Http\Requests\PreRegistroAjaxRequest;
use App\Http\Requests\PreRegistroRequest;
use App\Repositories\GerentiRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserExternoSiteController extends Controller
{
    private $service;
    private $gerentiRepository;

    public function __construct(MediadorServiceInterface $service, GerentiRepositoryInterface $gerentiRepository)
    {        
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
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao criar o cadastro no Login Externo');
        }

        if(isset($dados['message']))
            return redirect()->route('externo.cadastro')->withInput()->with($dados);

        return view('site.agradecimento')->with([
            'agradece' => 'Cadastro no Login Externo realizado com sucesso. Por favor, <strong>acesse o email informado para confirmar seu cadastro.</strong>',
            'link_temp' => route('externo.verifica-email', [
                'tipo' => $this->service->getService('UserExterno')->getDefinicoes($validated['tipo_conta'])['variavel_url'], 
                'token' => $dados->verify_token
            ]),
        ]);
    }

    public function verificaEmail($tipo, $token)
    {
        try{
            $erro = $this->service->getService('UserExterno')->verificaEmail($token, $tipo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao atualizar a verificação de email do cadastro no Login Externo');
        }
        
        $success = [
            'message' => 'Email verificado com sucesso. Favor continuar com o login abaixo.',
            'class' => 'alert-success'
        ];

        return redirect()->route('externo.login')->with(isset($erro['message']) ? $erro : $success);
    }

    public function index()
    {
        return view('site.userExterno.home');
    }

    public function editarView()
    {
        $resultado = auth()->guard(getGuardExterno(auth()))->user();

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
            $tipo = $validate['tipo_conta'];
            unset($validate['tipo_conta']);
            $externo = auth()->guard(getGuardExterno(auth()))->user();
            $erro = $this->service->getService('UserExterno')->editDados($validate, $externo, $tipo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao atualizar os dados cadastrais no Login Externo');
        }

        if(isset($erro['message']) && isset($validate['password_atual']))
            return redirect()->route('externo.editar.senha.view')->with($erro);

        return isset($erro['message']) ? redirect()->route('externo.editar.view')->with($erro)->withInput() : 
            redirect()->route('externo.editar.view')->with([
                'message' => 'Dados alterados com sucesso.',
                'class' => 'alert-success'
            ]);
    }

    // PRE-REGISTRO ******************************************************************************************************************

    public function preRegistrosRelacao()
    {
        try{
            $externo = auth()->guard('contabil')->user();
            $dados = $this->service->getService('PreRegistro')->getPreRegistros($externo);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao listar as solicitações de registro');
        }

        return view('site.userExterno.pre-registros', $dados);
    }
    
    public function preRegistroView($preRegistro = null)
    {
        try{
            if(isset($preRegistro) && !auth()->guard('contabil')->check())
                return redirect()->route('externo.dashboard');

            $pr = isset($preRegistro) && auth()->guard('contabil')->check() ? 
            auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($preRegistro) :
            auth()->guard('user_externo')->user();

            $externo = isset($preRegistro) && auth()->guard('contabil')->check() ? $pr->userExterno : $pr;

            $gerenti = null;
            $resultado = null;
            
            if(isset($externo)){
                $dados = $this->service->getService('PreRegistro')->verificacao($this->gerentiRepository, $externo);
                $gerenti = $dados['gerenti'];
                if(!isset($gerenti))
                    $resultado = isset($preRegistro) ? $pr : $externo->load('preRegistro')->preRegistro;
            }
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return redirect()->route('externo.relacao.preregistros')->with([
                'message' => 'Não existe solicitação de registro com esta ID relacionada com a sua contabilidade.',
                'class' => 'alert-danger'
            ]);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao verificar os dados para permitir ou não a solicitação de registro');
        }

        return view('site.userExterno.pre-registro', compact('resultado', 'gerenti'));
    }

    public function contabilCriarPreRegistro(PreRegistroRequest $request)
    {
        try{
            $externo = auth()->guard('contabil')->user();
            $validated = $request->validated();
            $dados = $this->service->getService('PreRegistro')->setPreRegistro($this->gerentiRepository, $this->service, $externo, $validated);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, 'Erro ao verificar os dados para permitir ou não a solicitação de registro');
        }

        if(isset($dados['message']))
            return redirect()->route('externo.preregistro.view')->with($dados);
        return redirect()->route('externo.inserir.preregistro.view', $dados->id);
    }

    public function inserirPreRegistroView(Request $request, $preRegistro = null)
    {
        try{
            if(($request->checkPreRegistro != 'on') && auth()->guard('user_externo')->check())
                return redirect()->route('externo.preregistro.view');

            if(isset($preRegistro) && !auth()->guard('contabil')->check())
                return redirect()->route('externo.dashboard');

            $externo = isset($preRegistro) && auth()->guard('contabil')->check() ? 
            auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($preRegistro)->userExterno :
            auth()->guard('user_externo')->user();

            if(!isset($externo) && auth()->guard('contabil')->check())
                return redirect()->route('externo.relacao.preregistros');

            $dados = $this->service->getService('PreRegistro')->verificacao($this->gerentiRepository, $externo);

            if(isset($dados['gerenti']))
                return isset($preRegistro) ? 
                redirect()->route('externo.preregistro.view', $preRegistro)->with(['resultado' => null, 'gerenti' => $dados['gerenti']]) : 
                redirect()->route('externo.preregistro.view')->with(['resultado' => null, 'gerenti' => $dados['gerenti']]);

            $dados = $this->service->getService('PreRegistro')->getPreRegistro($this->service, $externo);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return redirect()->route('externo.relacao.preregistros')->with([
                'message' => 'Não existe solicitação de registro com esta ID relacionada com a sua contabilidade.',
                'class' => 'alert-danger'
            ]);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao carregar os dados da solicitação de registro');
        }

        if(isset($dados['message']))
            return isset($preRegistro) ? redirect()->route('externo.preregistro.view', $preRegistro)->with($dados) : 
                redirect()->route('externo.preregistro.view')
                ->with($dados)
                ->with(['resultado' => null, 'gerenti' => null]);

        return view('site.userExterno.inserir-pre-registro', $dados);
    }

    public function inserirPreRegistroAjax(PreRegistroAjaxRequest $request, $preRegistro = null)
    {
        try{
            if(isset($preRegistro) && !auth()->guard('contabil')->check())
                return redirect()->route('externo.dashboard');

            $externo = isset($preRegistro) && auth()->guard('contabil')->check() ? 
            auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($preRegistro)->userExterno :
            auth()->guard('user_externo')->user();

            $contabil = auth()->guard('contabil')->user();
            
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveSiteAjax($validatedData, $this->gerentiRepository, $externo, $contabil);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, 'Não existe solicitação de registro com esta ID relacionada com a sua contabilidade.');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao salvar os dados da solicitação de registro via ajax');
        }
        
        return response()->json($dados);
    }

    public function verificaPendenciaPreRegistro(PreRegistroRequest $request, $preRegistro = null)
    {
        try{
            if(isset($preRegistro) && !auth()->guard('contabil')->check())
                return redirect()->route('externo.dashboard');

            $externo = isset($preRegistro) && auth()->guard('contabil')->check() ? 
            auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($preRegistro)->userExterno :
            auth()->guard('user_externo')->user();
            
            $dados = $this->service->getService('PreRegistro')->verificacao($this->gerentiRepository, $externo);
            
            if(isset($dados['gerenti']))
                return isset($preRegistro) ? 
                redirect()->route('externo.preregistro.view', $preRegistro)->with(['resultado' => null, 'gerenti' => $dados['gerenti']]) : 
                redirect()->route('externo.preregistro.view')->with(['resultado' => null, 'gerenti' => $dados['gerenti']]);

            if(!isset($externo) && auth()->guard('contabil')->check())
                return redirect()->route('externo.relacao.preregistros');

            $dados = $this->service->getService('PreRegistro')->getPreRegistro($this->service, $externo);

            if(isset($dados['message']))
                return isset($preRegistro) ? redirect()->route('externo.preregistro.view', $preRegistro)->with($dados) : 
                    redirect()->route('externo.preregistro.view')
                    ->with($dados)
                    ->with(['resultado' => null, 'gerenti' => null]);

            $resultado = $dados['resultado'];

            if(!$resultado->userPodeEditar())
                throw new \Exception('Não autorizado a editar o formulário com a solicitação em análise ou finalizada', 401);

            $dados['semPendencia'] = true;
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return redirect()->route('externo.relacao.preregistros')->with([
                'message' => 'Não existe solicitação de registro com esta ID relacionada com a sua contabilidade.',
                'class' => 'alert-danger'
            ]);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao verificar pendências da solicitação de registro');
        }
        
        return view('site.userExterno.inserir-pre-registro', $dados);
    }

    // Esse request não devolve a página para correção.
    // Apenas valida os dados já salvos no bd que foram carregados no form novamente ou via request direto
    // Apenas rota de confirmação do envio e onde é realizado os processos
    public function inserirPreRegistro(PreRegistroRequest $request, $preRegistro = null)
    {
        try{
            if(isset($preRegistro) && !auth()->guard('contabil')->check())
                return redirect()->route('externo.dashboard');

            $externo = isset($preRegistro) && auth()->guard('contabil')->check() ? 
            auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($preRegistro)->userExterno :
            auth()->guard('user_externo')->user();

            $contabil = auth()->guard('contabil')->user();

            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveSite($validatedData, $this->gerentiRepository, $externo, $contabil);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            return redirect()->route('externo.relacao.preregistros')->with([
                'message' => 'Não existe solicitação de registro com esta ID relacionada com a sua contabilidade.',
                'class' => 'alert-danger'
            ]);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao enviar os dados da solicitação de registro para análise');
        }
        
        return isset($preRegistro) ? redirect()->route('externo.preregistro.view', $preRegistro)->with($dados) : 
        redirect()->route('externo.preregistro.view')->with($dados);
    }

    public function preRegistroAnexoDownload($id, $preRegistro = null)
    {
        try{
            if(isset($preRegistro) && !auth()->guard('contabil')->check())
                return redirect()->route('externo.dashboard');

            $externo = isset($preRegistro) && auth()->guard('contabil')->check() ? 
            auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($preRegistro)->userExterno :
            auth()->guard('user_externo')->user();

            $contabil = auth()->guard('contabil')->user();
            $preRegistro = isset($externo) ? $externo->load('preRegistro')->preRegistro : null;

            if(!isset($preRegistro))
                throw new \Exception('Não autorizado a acessar a solicitação de registro', 401);

            $file = $this->service->getService('PreRegistro')->downloadAnexo($id, $preRegistro->id, $contabil);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, 'Não existe solicitação de registro com esta ID relacionada com a sua contabilidade.');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao solicitar download do arquivo');
        }
        
        return response()->file($file, ["Cache-Control" => "no-cache"]);
    }

    public function preRegistroAnexoExcluir($id, $preRegistro = null)
    {
        try{
            if(isset($preRegistro) && !auth()->guard('contabil')->check())
                return redirect()->route('externo.dashboard');
            
            $externo = isset($preRegistro) && auth()->guard('contabil')->check() ? 
            auth()->guard('contabil')->user()->load('preRegistros')->preRegistros()->findOrFail($preRegistro)->userExterno :
            auth()->guard('user_externo')->user();

            $contabil = auth()->guard('contabil')->user();

            $dados = $this->service->getService('PreRegistro')->excluirAnexo($id, $externo, $contabil);
        } catch(ModelNotFoundException $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(404, 'Não existe solicitação de registro com esta ID relacionada com a sua contabilidade.');
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao solicitar exclusão do arquivo');
        }
        
        return response()->json($dados);
    }
}