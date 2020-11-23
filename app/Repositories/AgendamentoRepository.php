<?php

namespace App\Repositories;

use App\Agendamento;
use Illuminate\Support\Facades\DB;

class AgendamentoRepository 
{
    public function getToTable($idregional)
    {
        return Agendamento::where('dia','=', date('Y-m-d'))
            ->where('idregional', $idregional)
            ->orderBy('dia', 'ASC')
            ->orderBy('hora', 'ASC')
            ->paginate(25);
    }

    public function store($dados)
    {
        return Agendamento::create($dados);
    }

    public function getById($id) 
    {
        return Agendamento::findOrFail($id);
    }

    public function getToBusca($criterio) 
    {
        return Agendamento::where('nome','LIKE','%' . $criterio . '%')
            ->orWhere('idagendamento','LIKE', $criterio)
            ->orWhere('cpf','LIKE','%' . $criterio . '%')
            ->orWhere('email','LIKE','%' . $criterio . '%')
            ->orWhere('protocolo','LIKE','%' . $criterio . '%')
            ->paginate(25);
    }

    public function update($id, $data, $agendamento = null) 
    {
        if($agendamento) {
            return $agendamento->update($data);
        }

        return Agendamento::findOrFail($id)->update($data);
    }

    public function getAllPastAgendamentoPendente()
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->whereNull('status')
            ->orderBy('dia', 'DESC')
            ->paginate(10);
    }

    public function getCountPastAgendamentoPendente()
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->whereNull('status')
            ->count();
    }

    public function getAllPastAgendamentoPendenteSede()
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->where('idregional', 1)
            ->whereNull('status')
            ->orderBy('dia', 'DESC')
            ->paginate(10);
    }

    public function getCountPastAgendamentoPendenteSede()
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->where('idregional', 1)
            ->whereNull('status')
            ->count();
    }

    public function getAllPastAgendamentoPendenteSeccionais()
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->where('idregional', '!=', 1)
            ->whereNull('status')
            ->orderBy('dia', 'DESC')
            ->paginate(10);
    }

    public function getCountPastAgendamentoPendenteSeccionais()
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->where('idregional', '!=', 1)
            ->whereNull('status')
            ->count();
    }

    public function getPastAgendamentoPendenteByRegional($idRegional)
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->where('idregional', '=', $idRegional)
            ->whereNull('status')
            ->orderBy('dia', 'DESC')
            ->paginate(10);
    }

    public function getCountPastAgendamentoPendenteByRegional($idRegional)
    {
        return Agendamento::where('dia', '<', date('Y-m-d'))
            ->where('idregional', '=', $idRegional)
            ->whereNull('status')
            ->count();
    }

    public function getCountAgendamentoNaoCompareceuByCpf($cpf)
    {
        return Agendamento::where('cpf', $cpf)
            ->where('status', Agendamento::STATUS_NAO_COMPARECEU)
            ->whereBetween('dia',[date('Y-m-d', strtotime('-90 days')), date('Y-m-d')])
            ->count();
    }

    public function getCountAgendamentoPendenteByCpfDay($dia, $cpf)
    {
        return Agendamento::where('dia', $dia)
            ->where('cpf', $cpf)
            ->whereNull('status')
            ->count();
    }

    public function getAgendamentoPendeteByDiaHoraRegional($dia, $hora, $idregional)
    {
        return Agendamento::where('dia', $dia)
            ->where('hora', $hora)
            ->where('idregional', $idregional)
            ->whereNull('status')
            ->get();
    }

    public function getAgendamentoPendenteByDiaRegional($dia, $idregional)
    {
        return Agendamento::where('dia', $dia)
            ->where('idregional', $idregional)
            ->whereNull('status')
            ->get();
    }

    public function getToConsulta($protocolo)
    {
        return  Agendamento::where('protocolo', $protocolo)
            ->where('dia','>=', date('Y-m-d'))
            ->first();
    }

    public function checkProtocol($protocolo)
    {
        return  Agendamento::where('protocolo', $protocolo)->count();
    }
   
    public function getToTableFilter($mindia, $maxdia, $regional, $status)
    {
        $resultados = Agendamento::whereBetween('dia',[$mindia,$maxdia]);

        if(!empty($regional)) {
            $resultados->where('idregional', $regional);
        }

        if(!empty($status)) {
            $resultados->where('status', $status);
        }

        return $resultados->orderBy('idregional','ASC')
            ->orderBy('dia','DESC')
            ->orderBy('hora','ASC')
            ->limit(50)
            ->paginate(25);
    }

    public function getAgendamentoConcluidoCountByRegional($idregional) 
    {
        return Agendamento::select(DB::raw("idusuario, count(1) as contagem"))
            ->where("status", Agendamento::STATUS_COMPARECEU)
            ->where("idregional", $idregional)
            ->groupBy("idusuario")
            ->orderBy('contagem', 'DESC')
            ->get();
    }
}