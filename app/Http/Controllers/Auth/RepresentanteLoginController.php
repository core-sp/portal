<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Representante;
use Illuminate\Support\Facades\Auth;
use App\Rules\CpfCnpj;
use App\Traits\GerentiProcedures;
use Illuminate\Support\Facades\Input;

class RepresentanteLoginController extends Controller
{
    use GerentiProcedures;

    public function __construct()
    {
        $this->middleware('guest:representante')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.representante-login'); 
    }

    protected function verificaGerentiLogin($cpfCnpj)
    {
        $cpfCnpj = preg_replace('/[^0-9]+/', '', $cpfCnpj);
        $registro = Representante::where('cpf_cnpj', $cpfCnpj)->first();

        if(isset($registro)) {
            $checkGerenti = $this->checaAtivo($registro->registro_core, $cpfCnpj);

            if($checkGerenti === false) {
                return redirect()
                    ->route('representante.cadastro')
                    ->with('message', 'Desculpe, mas o cadastro informado não está corretamente inscrito no Core-SP. Por favor, verifique se todas as informações foram inseridas corretamente.')
                    ->withInput(Input::all());
            }
        }
    }

    protected function verificaSeAtivo($cpfCnpj)
    {
        $representante = Representante::where('cpf_cnpj', '=', $cpfCnpj)->first();

        if($representante->ativo === 0)
            return false;
        else
            return true;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'cpf_cnpj' => ['required', new CpfCnpj],
            'password' => 'required'
        ], [
            'required' => 'Campo obrigatório'
        ]);

        $cpfCnpj = preg_replace('/[^0-9]+/', '', $request->cpf_cnpj);

        if(!$this->verificaSeAtivo($cpfCnpj))
            return $this->redirectWithErrors($request->only('cpf_cnpj', 'remember'));

        $this->verificaGerentiLogin($request->cpf_cnpj);

        if (Auth::guard('representante')->attempt([
            'cpf_cnpj' => $cpfCnpj,
            'password' => $request->password
        ], $request->remember)) {
            return redirect()->intended(route('representante.dashboard'));
        }

        return $this->redirectWithErrors($request->only('cpf_cnpj', 'remember'));
    }

    protected function redirectWithErrors($withInput)
    {
        return redirect()
            ->back()
            ->with([
                'message' => 'Login inválido.',
                'class' => 'alert-danger'
            ])->withInput($withInput);
    }

    public function logout(Request $request)
    {
        Auth::guard('representante')->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
