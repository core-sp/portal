<?php

namespace App\Http\Controllers;

use App\TermoConsentimento;
use App\Repositories\TermoConsentimentoRepository;
use App\Events\CrudEvent;
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
        $this->termoConsentimentoRepository->create(request()->ip(), $request->registro_core);

        event(new CrudEvent('termo de consentimento aceito', 'registrado no banco', $request->id));

        return redirect('/termo-de-consentimento')
                ->with('message', 'O seu registro CORE foi salvo com sucesso para continuar recebendo nossos e-mails.')
                ->with('class', 'alert-success');
    }
}
