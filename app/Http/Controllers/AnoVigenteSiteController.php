<?php

namespace App\Http\Controllers;

use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use App\Repositories\GerentiRepositoryInterface;
use App\Contracts\MediadorServiceInterface;

class AnoVigenteSiteController extends Controller
{
    private $gerentiRepository;
    private $service;

    public function __construct(GerentiRepositoryInterface $gerentiRepository, MediadorServiceInterface $service)
    {
        $this->gerentiRepository = $gerentiRepository;
        $this->service = $service;
    }

    public function anoVigenteView()
    {
        $aviso = $this->service->getService('Aviso')->getByArea($this->service->getService('Aviso')->areas()[2]);
        $aviso = isset($aviso) && $aviso->isAtivado() ? $aviso : null;
        return view('site.anuidade-ano-vigente', ['aviso' => $aviso]);
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

        if(isset($nossonumero[0]['NOSSONUMERO'])) {
            return redirect()->back()->with('nossonumero', $nossonumero)->withInput();
        } else {
            $notFound = true;
            return redirect()->back()->with('notFound', $notFound)->withInput();
        }
    }
}
