<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TermoConsentimentoRequest;
use App\Contracts\MediadorServiceInterface;

class TermoConsentimentoController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->service = $service;
    }

    public function termoConsentimentoView()
    {
        return view('site.termo-consentimento');
    }

    public function termoConsentimento(TermoConsentimentoRequest $request)
    {
        try {
            $validated = (object) $request->validated();
            $message = $this->service->getService('TermoConsentimento')->save(request()->ip(), $validated->email);
        } catch(\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao salvar os dados no Termo de Consentimento.");
        }

        return redirect(route('termo.consentimento.view'))->with([
            'message' => isset($message['message']) ? $message['message'] : 'E-mail cadastrado com sucesso para continuar recebendo nossos informativos.',
            'class' => isset($message['class']) ? $message['class'] : 'alert-success'
        ]);
    }

    public function termoConsentimentoPdf()
    {
        try {
            $file = $this->service->getService('TermoConsentimento')->caminhoFile();
        } catch(\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar o arquivo do Termo de Consentimento.");
        }

        return isset($file) ? response()->file($file, ['Cache-Control' => 'no-cache, no-store, must-revalidate']) : redirect()->back();
    }

    public function download()
    {
        abort_if(!in_array(auth()->user()->idperfil, [1, 3]), 403);
        
        try {
            $file = $this->service->getService('TermoConsentimento')->download();
        } catch(\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao carregar a lista do Termo de Consentimento");
        }

        return isset($file) ? $file : redirect(route('admin'))->with([
            'message' => 'Não há emails cadastrados na tabela de Termo de Consentimento.',
            'class' => 'alert-warning'
        ]);
    }
}
