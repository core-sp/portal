<?php

namespace App\Http\Controllers;

use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use App\Repositories\GerentiRepositoryInterface;

class AnoVigenteSiteController extends Controller
{
    private $gerentiRepository;

    public function __construct(GerentiRepositoryInterface $gerentiRepository)
    {
        $this->gerentiRepository = $gerentiRepository;
    }

    public function anoVigenteView()
    {
        return view('site.anuidade-ano-vigente');
    }

    public function anoVigente(Request $request)
    {
        $cpfCnpj = apenasNumeros(request('cpfCnpj'));

        $request->request->set('cpfCnpj', $cpfCnpj);

        $this->validate($request, [
            'cpfCnpj' => ['required', new CpfCnpj],
            'g-recaptcha-response' => 'required|recaptcha'
        ], [
            'cpfCnpj.required' => 'Informe o CPF/CNPJ',
            'g-recaptcha-response' => 'ReCAPTCHA inválido',
            'g-recaptcha-response.required' => 'ReCAPTCHA obrigatório'
        ]);

        $nossonumero = $this->gerentiRepository->gerentiAnuidadeVigente($cpfCnpj);

        if(!empty($nossonumero)) {
            return redirect()->back()->with('nossonumero', $nossonumero)->withInput();
        } else {
            $notFound = true;
            return redirect()->back()->with('notFound', $notFound)->withInput();
        }
    }
}
