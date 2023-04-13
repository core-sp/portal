<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GeralRequest;
use App\Contracts\MediadorServiceInterface;

class NewsletterController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service)
    {
        $this->middleware('auth', ['except' => ['store']]);
        $this->service = $service;
    }

    public function store(GeralRequest $request)
    {
        try{
            $validated = $request->validated();
            $validated['ip'] = $request->ip();
            $agradece = $this->service->getService('Geral')->newsletter($validated);
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao salvar os dados para a newsletter no portal.");
        }

        return view('site.agradecimento')->with('agradece', $agradece);
    }

    public function download()
    {
        $this->authorize('viewAny', auth()->user());

        try{
            $dados = $this->service->getService('Geral')->newsletterAdmin();
            $arquivo = $dados['arquivo'];
            $headers = $dados['headers'];
        } catch (\Exception $e) {
            \Log::error('[Erro: '.$e->getMessage().'], [Controller: ' . request()->route()->getAction()['controller'] . '], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            abort(500, "Erro ao realizar o download da newsletter.");
        }

        return response()->stream($arquivo, 200, $headers);
    }
}
