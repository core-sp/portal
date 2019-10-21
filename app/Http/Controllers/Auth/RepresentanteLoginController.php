<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Rules\CpfCnpj;

class RepresentanteLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:representante')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.representante-login'); 
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'cpf_cnpj' => ['required', new CpfCnpj],
            'password' => 'required'
        ], [
            'required' => 'Campo obrigatório'
        ]);

        if (Auth::guard('representante')->attempt([
            'cpf_cnpj' => preg_replace('/[^0-9]+/', '', $request->cpf_cnpj),
            'password' => $request->password
        ], $request->remember)) {
            return redirect()->intended(route('representante.dashboard'));
        }

        return redirect()
            ->back()
            ->with([
                'message' => 'Login inválido.',
                'class' => 'alert-danger'
            ])->withInput($request->only('cpf_cnpj', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::guard('representante')->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
