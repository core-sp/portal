<?php

namespace App\Http\Controllers;

use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Http\Requests\PreRepresentanteRequest;
use App\Mail\CadastroPreRepresentanteMail;
use App\Repositories\PreRepresentanteRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PreRepresentanteSiteController extends Controller
{
    private $prerepresentanteRepository;

    public function __construct(PreRepresentanteRepository $prerepresentanteRepository)
    {
        $this->middleware('auth:pre_representante')->except(['cadastroView', 'cadastro', 'verificaEmail']);
        $this->prerepresentanteRepository = $prerepresentanteRepository;
    }

    public function cadastroView()
    {
        return view('site.prerepresentante.cadastro');
    }

    public function cadastro(PreRepresentanteRequest $request)
    {
        $validated = (object) $request->validated();
        $checkSoftDeleted = $this->prerepresentanteRepository->getDeletadoNaoAtivo($validated->cpf_cnpj_cad);
        $token = str_random(32);

        try{
            if($checkSoftDeleted)
                $prerepresentante = $this->prerepresentanteRepository->update($checkSoftDeleted->id, $validated, $token);
            else
                $prerepresentante = $this->prerepresentanteRepository->store($validated, $token);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, 'Erro ao criar o cadastro no Pré-registro');
        }

        $body = '<strong>Cadastro no Pré-registro do Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('prerepresentante.verifica-email', $token) .'">NESTE LINK</a>.';

        Mail::to($validated->email)->queue(new CadastroPreRepresentanteMail($body));
        event(new ExternoEvent('"' . $validated->cpf_cnpj_cad . '" ("' . $validated->email . '") cadastrou-se na Área do Pré-registro.'));

        return view('site.agradecimento')->with([
            'agradece' => 'Cadastro no Pré-registro realizado com sucesso. Por favor, <strong>acesse o email informado para confirmar seu cadastro.</strong>'
        ]);
    }

    public function verificaEmail($token)
    {
        $prerepresentante = $this->prerepresentanteRepository->getByToken($token);
        if($prerepresentante) {
            try{
                $prerep = $this->prerepresentanteRepository->updatePosVerificarEmail($prerepresentante);
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                abort(500, 'Erro ao atualizar a verificação de email do cadastro no Pré-registro');
            }
        } else 
            abort(500, 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Pré-registro, caso contrário, por favor refazer cadastro no Pré-registro.');

        event(new ExternoEvent('Pré Representante ' . $prerepresentante->id . ' ("'. $prerepresentante->cpf_cnpj .'") verificou o email após o cadastro.'));

        return redirect(route('prerepresentante.login'))
            ->with([
                'message' => 'Email verificado com sucesso. Favor continuar com o login abaixo.',
                'class' => 'alert-success'
            ]);
    }

    public function index()
    {
        return view('site.prerepresentante.home');
    }

    public function editarView()
    {
        $resultado = auth()->guard('pre_representante')->user();

        return view('site.prerepresentante.dados', compact('resultado'));
    }

    public function editarSenhaView()
    {
        $alterarSenha = true;
        return view('site.prerepresentante.dados', compact('alterarSenha'));
    }

    public function editar(PreRepresentanteRequest $request)
    {
        $validate = (object) $request->validated();
        $prerep = auth()->guard('pre_representante')->user();
        if(isset($validate->password))
        {
            try{
                $update = $this->prerepresentanteRepository->updateSenha($prerep, $validate);
                if(!$update)
                    return redirect(route('prerepresentante.editar.senha.view'))->with([
                        'message' => 'A senha atual digitada está incorreta!',
                        'class' => 'alert-danger',
                    ]);
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                abort(500, 'Erro ao atualizar a senha no Pré-registro');
            }
        }else
        {
            try{
                $update = $this->prerepresentanteRepository->updateEditarNomeEmail($prerep->id, $validate);
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                abort(500, 'Erro ao atualizar os dados no Pré-registro');
            }
        }

        event(new ExternoEvent('Pré Representante ' . $prerep->id . ' ("'. $prerep->cpf_cnpj .'") alterou os dados com sucesso.'));

        return redirect(route('prerepresentante.editar.view'))->with([
            'message' => 'Dados alterados com sucesso.',
            'class' => 'alert-success'
        ]);
    }
    
    public function preRegistroView()
    {
        $resultado = null;
        return view('site.prerepresentante.pre-registro', compact('resultado'));
    }

    public function inserirPreRegistroView()
    {
        $prerep = auth()->guard('pre_representante')->user();
        // temporário
        $estados_civil = ['Casado(a)', 'Solteiro(a)', 'Viúvo(a)'];
        $nacionalidades = ['Brasileira', 'Portuguesa'];
        return view('site.prerepresentante.inserir-pre-registro', compact('prerep', 'estados_civil', 'nacionalidades'));
    }
}