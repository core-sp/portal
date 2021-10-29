<?php

namespace App\Http\Controllers;

use App\PreRepresentante;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Http\Requests\PreRepresentanteRequest;
use App\Mail\CadastroPreRepresentanteMail;
use App\Repositories\PreRepresentanteRepository;
use App\Repositories\RepresentanteRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PreRepresentanteSiteController extends Controller
{
    private $prerepresentanteRepository;
    private $representanteRepository;

    public function __construct(PreRepresentanteRepository $prerepresentanteRepository, RepresentanteRepository $representanteRepository)
    {
        $this->middleware('auth:pre_representante')->except(['cadastroView', 'cadastro', 'verificaEmail']);
        $this->prerepresentanteRepository = $prerepresentanteRepository;
        $this->representanteRepository = $representanteRepository;
    }

    public function cadastroView()
    {
        return view('site.prerepresentante.cadastro');
    }

    public function cadastro(PreRepresentanteRequest $request)
    {
        $validated = (object) $request->validated();
        $validated->cpf_cnpj = apenasNumeros(request('cpf_cnpj'));
        // verificar 'se existir no gerenti'
        if($this->prerepresentanteRepository->getByCpfCnpj($validated->cpf_cnpj))
            return redirect(route('prerepresentante.cadastro'))
                ->with([
                    'message' => 'Esse CPF / CNPJ já está cadastrado.',
                    'class' => 'alert-warning'
                ]);
        if($this->representanteRepository->getByCpfCnpj($validated->cpf_cnpj))
            return redirect(route('prerepresentante.cadastro'))
                ->with([
                    'message' => 'Já existe esse CPF / CNPJ no nosso cadastro de Representante Comercial.',
                    'class' => 'alert-warning'
                ]);
        $checkSoftDeleted = $this->prerepresentanteRepository->jaExiste($validated->cpf_cnpj);
        $token = str_random(32);

        try{
            if($checkSoftDeleted)
                $prerepresentante = $this->prerepresentanteRepository->update($checkSoftDeleted->id, $validated, $token);
            else
                $prerepresentante = $this->prerepresentanteRepository->store($validated, $token);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, 'Erro ao criar o cadastro no Pré Registro');
        }

        $body = '<strong>Cadastro no Pré Registro do Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('prerepresentante.verifica-email', $token) .'">NESTE LINK</a>.';

        Mail::to($validated->email)->queue(new CadastroPreRepresentanteMail($body));
        event(new ExternoEvent('"' . request('cpf_cnpj') . '" ("' . request('email') . '") cadastrou-se na Área do Pré Registro.'));

        return view('site.agradecimento')->with([
            'agradece' => 'Cadastro no Pré Registro realizado com sucesso. Por favor, <strong>acesse o email informado para confirmar seu cadastro.</strong>'
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
                abort(500, 'Erro ao atualizar a verificação de email do cadastro no Pré Registro');
            }
        } else 
            abort(500, 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Pré Registro, caso contrário, por favor refazer cadastro no Pré Registro.');

        event(new ExternoEvent('Pré Representante ' . $prerepresentante->id . ' ("'. $prerepresentante->cpf_cnpj .'") verificou o email após o cadastro.'));

        return redirect(route('prerepresentante.login'))
            ->with([
                'message' => 'Email verificado com sucesso. Favor continuar com o login abaixo.',
                'class' => 'alert-success'
            ]);
    }

}