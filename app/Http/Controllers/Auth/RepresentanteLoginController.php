<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
            'cpf_cnpj' => 'required',
            'password' => 'required'
        ]);

        if (Auth::guard('representante')->attempt([
            'cpf_cnpj' => $request->cpf_cnpj,
            'password' => $request->password
        ], $request->remember)) {
            return redirect()->intended(route('representante.dashboard'));
        }

        return redirect()->back()->withInput($request->only('cpf_cnpj', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::guard('representante')->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
