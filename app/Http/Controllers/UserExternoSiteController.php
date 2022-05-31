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
        $this->middleware('auth:user_externo')->except(['cadastroView', 'cadastro', 'verificaEmail']);
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
            \Log::error($e->getMessage());
            abort(500, 'Erro ao criar o cadastro no Login Externo');
        }

        return view('site.agradecimento')->with([
            'agradece' => 'Cadastro no Login Externo realizado com sucesso. Por favor, <strong>acesse o email informado para confirmar seu cadastro.</strong>'
        ]);
    }

    public function verificaEmail($token)
    {
        try{
            $erro = $this->service->getService('UserExterno')->verificaEmail($token);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
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
            $erro = $this->service->getService('UserExterno')->editDados($validate);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
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
            $dados = $this->service->getService('PreRegistro')->verificacao($this->gerentiRepository, auth()->guard('user_externo')->user());
            $resultado = $dados['resultado'];
            $gerenti = $dados['gerenti'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, 'Erro ao verificar os dados para permitir ou não a solicitação de registro');
        }

        return view('site.userExterno.pre-registro', compact('resultado', 'gerenti'));
    }

    public function inserirPreRegistroView()
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $verificacao = $this->service->getService('PreRegistro')->verificacao($this->gerentiRepository, $externo);
            if(isset($verificacao['gerenti']))
                return view('site.userExterno.pre-registro', ['resultado' => null, 'gerenti' => $verificacao['gerenti']]);
                
            $dados = $this->service->getService('PreRegistro')->getPreRegistro($this->service, $verificacao['resultado'], $externo);
            $codigos = $dados['codigos'];
            $resultado = $dados['resultado'];
            $regionais = $dados['regionais'];
            $classes = $dados['classes'];
            $totalFiles = $dados['totalFiles'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, 'Erro ao carregar os dados da solicitação de registro');
        }

        return view('site.userExterno.inserir-pre-registro', compact('resultado', 'regionais', 'totalFiles', 'codigos', 'classes'));
    }

    public function inserirPreRegistroAjax(PreRegistroAjaxRequest $request)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveSiteAjax($validatedData, $this->gerentiRepository, $externo);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao salvar os dados da solicitação de registro via ajax');
        }
        
        return response()->json($dados);
    }

    public function inserirPreRegistro(PreRegistroRequest $request)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $validatedData = $request->validated();
            $dados = $this->service->getService('PreRegistro')->saveSite($validatedData, $this->gerentiRepository, $externo);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao enviar os dados da solicitação de registro para análise');
        }
        
        return redirect(route('externo.preregistro.view'))->with($dados);
    }

    public function preRegistroAnexoDownload($id)
    {
        try{
            $externo = auth()->guard('user_externo')->user();
            $file = $this->service->getService('PreRegistro')->downloadAnexo($id, $externo);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
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
            \Log::error($e->getMessage());
            in_array($e->getCode(), [401]) ? abort($e->getCode(), $e->getMessage()) : 
            abort(500, 'Erro ao solicitar exclusão do arquivo');
        }
        
        return response()->json($dados);
    }
}