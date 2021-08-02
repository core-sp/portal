<?php

namespace App\Http\Controllers;

use App\TermoConsentimento;
use App\Repositories\TermoConsentimentoRepository;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class TermoConsentimentoController extends Controller
{
    private $termoConsentimentoRepository;

    public function __construct(TermoConsentimentoRepository $termoConsentimentoRepository)
    {
        $this->termoConsentimentoRepository = $termoConsentimentoRepository;
    }

    public function termoConsentimentoView()
    {
        return view('site.termo-consentimento');
    }

    public function termoConsentimento(Request $request)
    {
        $regras = [
            'email' => 'required|email|max:191'
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório',
            'max' => 'Excedido limite de caracteres',
            'email' => 'Email inválido'
        ];

        $errors = $request->validate($regras, $mensagens);

        $ja_existe = $this->termoConsentimentoRepository->getByEmail($request->email);

        if($ja_existe)
        {
            return redirect('/termo-de-consentimento')
                ->with('message', 'E-mail já cadastrado para continuar recebendo nossos informativos.')
                ->with('class', 'alert-warning');
        }

        $save = $this->termoConsentimentoRepository->create(request()->ip(), $request->email);

        if(!$save) {
            abort(500);
        }
        
        event(new ExternoEvent("foi criado um novo registro no termo de consentimento, com a id: " . $save->id));

        return redirect('/termo-de-consentimento')
                ->with('message', 'E-mail cadastrado com sucesso para continuar recebendo nossos informativos.')
                ->with('class', 'alert-success');
    }

    public function termoConsentimentoPdf()
    {
        return response()->file('arquivos/CORE-SP_Termo_de_consentimento.pdf');
    }
}
