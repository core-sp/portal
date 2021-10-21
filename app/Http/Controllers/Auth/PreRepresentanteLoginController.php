<?php

namespace App\Http\Controllers\Auth;

use App\PreRepresentante;
use App\Rules\CpfCnpj;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class PreRepresentanteLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:pre_representante')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.prerepresentante-login'); 
    }

    protected function verificaSeAtivo($cpfCnpj)
    {
        $prerepresentante = PreRepresentante::where('cpf_cnpj', $cpfCnpj)->first();

        if(isset($prerepresentante)) {
            if($prerepresentante->ativo === 0) {
                return [
                    'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta.',
                    'class' => 'alert-warning'
                ];
            } else {
                return [];
            }
        } else {
            return [
                'message' => 'Login inválido.',
                'class' => 'alert-danger'
            ];
        }
    }

    public function login(Request $request)
    {
        $cpfCnpj = apenasNumeros($request->cpf_cnpj);

        $request->request->set('cpf_cnpj', $cpfCnpj);

        $this->validate($request, [
            'cpf_cnpj' => ['required', new CpfCnpj],
            'password' => 'required'
        ], [
            'required' => 'Campo obrigatório'
        ]);

        if(!empty($this->verificaSeAtivo($cpfCnpj)))
            return $this->redirectWithErrors(
                $request->only('cpf_cnpj'), 
                $this->verificaSeAtivo($cpfCnpj)['message'], 
                $this->verificaSeAtivo($cpfCnpj)['class']
            );

        if (Auth::guard('pre_representante')->attempt([
            'cpf_cnpj' => $cpfCnpj,
            'password' => $request->password
        ])) {
            event(new ExternoEvent('Usuário ' . Auth::guard('pre_representante')->user()->id . ' conectou-se à Área do Pré Registro.'));

            return redirect()->intended(route('prerepresentante.dashboard'));
        }
        
        return $this->redirectWithErrors($request->only('cpf_cnpj'));
    }

    protected function redirectWithErrors($withInput, $message = 'Login inválido.', $class = 'alert-danger')
    {
        event(new ExternoEvent('Usuário com o cpf/cnpj ' .$withInput['cpf_cnpj']. ' não conseguiu logar.'));
        return redirect()
            ->back()
            ->with([
                'message' => $message,
                'class' => $class
            ])->withInput($withInput);
    }

    public function logout(Request $request)
    {
        // Removendo log de logout do representante para evitar problema quando a sessão expira e o representante tenta fazer logout
        // event(new ExternoEvent('Usuário ' . Auth::guard('representante')->user()->id . ' ("'. Auth::guard('representante')->user()->registro_core .'") desconectou-se da Área do Representante.'));

        Auth::guard('pre_representante')->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
