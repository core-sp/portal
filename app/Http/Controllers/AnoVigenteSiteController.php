<?php

namespace App\Http\Controllers;

use App\Rules\CpfCnpj;
use App\Traits\GerentiProcedures;
use Illuminate\Http\Request;

class AnoVigenteSiteController extends Controller
{
    use GerentiProcedures;

    public function anoVigenteView()
    {
        return view('site.anuidade-ano-vigente');
    }

    public function anoVigente(Request $request)
    {
        $cpfCnpj = preg_replace('/[^0-9]+/', '', request('cpfCnpj'));

        $request->request->set('cpfCnpj', $cpfCnpj);

        $this->validate($request, [
            'cpfCnpj' => ['required', new CpfCnpj],
            'g-recaptcha-response' => 'required|recaptcha'
        ], [
            'cpfCnpj.required' => 'Informe o CPF/CNPJ',
            'g-recaptcha-response' => 'ReCAPTCHA inválido',
            'g-recaptcha-response.required' => 'ReCAPTCHA obrigatório'
        ]);

        $nossonumero = $this->gerentiAnuidadeVigente($cpfCnpj);

        if(!empty($nossonumero)) {
            return view('site.anuidade-ano-vigente', compact('nossonumero'));
        } else {
            $notFound = true;
            return view('site.anuidade-ano-vigente', compact('notFound'));
        }
    }
}
