<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\UserExternoRequest;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Contracts\MediadorServiceInterface;

class UserExternoForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    private $service;
    private $tipo;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('guest');
        $this->middleware('guest:user_externo');
        $this->middleware('guest:contabil');
        $this->service = $service;
    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email-user-externo');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        $this->tipo = $this->service->getService('UserExterno')->getDefinicoes($request->tipo_conta);
        
        $cpf_cnpj = apenasNumeros($request->only('cpf_cnpj'));
        $email = $this->getEmail($cpf_cnpj);
        $arrayCC = [
            $this->tipo['campo'] => isset($email) ? $cpf_cnpj : ''
        ];
        
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink($arrayCC);
        
        if($response == Password::RESET_LINK_SENT)
        {
            event(new ExternoEvent('Usuário com o cpf/cnpj ' .$request->cpf_cnpj. ' do tipo de conta "'.$this->tipo['rotulo'].'" solicitou o envio de link para alterar a senha no Login Externo.'));
            return $this->sendResetLinkResponse($request, 'O link de reconfiguração de senha foi enviado ao email ' . $email . '<br>Esse link é válido por 60 minutos');
        }

        return $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        $msg = trans($response) == 'Não foi possível encontrar um usuário' ? 'CPF ou CNPJ não cadastrado ou conta não está ativa' : trans($response);

        return back()
                ->withInput($request->only('cpf_cnpj'))
                ->withErrors(['cpf_cnpj' => $msg]);
    }

    protected function validateEmail(Request $request)
    {
        $user_externo = new UserExternoRequest();
        $requestUser = Request::createFrom($request, $user_externo);
        $this->validate(
            $requestUser,
            $requestUser->rules(),
            $requestUser->messages()
        );
    }

    protected function broker()
    {
        return Password::broker($this->tipo['tabela']);
    }

    protected function getEmail($cpfCnpj)
    {
        $first = $this->service->getService('UserExterno')->findByCpfCnpj($this->tipo['tipo'], $cpfCnpj);
        
        if(isset($first) && ($first->ativo == 1))
            return $first->email;
        return null;
    }
}
