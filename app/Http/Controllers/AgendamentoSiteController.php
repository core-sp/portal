<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AgendamentoRequest;
use App\Contracts\MediadorServiceInterface;

class AgendamentoSiteController extends Controller
{
    private $service;

    public function __construct(MediadorServiceInterface $service) 
    {
        $this->service = $service;
    }

    public function formView()
    {
        try{
            $dados = $this->service->getService('Agendamento')->viewSite($this->service);
            $regionais = $dados['regionais'];
            $pessoas = $dados['pessoas'];
            $servicos = $dados['servicos'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para o agendamento.");
        }

        return view('site.agendamento', compact('regionais', 'pessoas', 'servicos'));
    }

    public function consultaView()
    {
        return view('site.agendamento-consulta');
    }

    public function consulta(AgendamentoRequest $request)
    {
        try{
            $validated = $request->validated();
            $resultado = $this->service->getService('Agendamento')->consultaSite($validated);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao consultar o protocolo do agendamento.");
        }

        return view('site.agendamento-consulta', compact('resultado'));
    }

    public function store(AgendamentoRequest $request)
    {
        try{
            $validated = $request->validated();
            $message = $this->service->getService('Agendamento')->saveSite($validated, $this->service);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao salvar os dados para criar o agendamento.");
        }

        return isset($message['message']) ? 
            redirect(route('agendamentosite.formview'))->with($message)->withInput($request->all()) : 
            view('site.agradecimento')->with($message);
    }

    public function cancelamento(AgendamentoRequest $request)
    {
        try{
            $validated = $request->validated();
            $validated['protocolo'] = request()->query('protocolo');
            $message = $this->service->getService('Agendamento')->cancelamentoSite($validated);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao cancelar o agendamento.");
        }

        return isset($message['message']) ? 
            redirect(route('agendamentosite.consultaView'))->with($message) : 
            view('site.agradecimento')->with('agradece', $message);
    }

    public function getDiasHorasAjax(Request $request)
    {
        try{
            $validate = $request->only('idregional', 'servico', 'dia');
            $dados = $this->service->getService('Agendamento')->getDiasHorasAjaxSite($validate, $this->service);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
        }

        return response()->json($dados);
    }

    public function regionaisPlantaoJuridico()
    {
        try{
            $dados = $this->service->getService('PlantaoJuridico')->getRegionaisAtivas();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            abort(500, "Erro ao carregar os dados para o agendamento via ajax.");
        }

        return response()->json($dados);
    }
}
