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

    public function getToBuscaByRegional($criterio, $idRegional) 
    {
        return Agendamento::where('idregional', $idRegional)
            ->where(function($query) use ($criterio) {
                $query->where('cpf','LIKE','%' . $criterio . '%')
                    ->orWhere('email','LIKE','%' . $criterio . '%')
                    ->orWhere('protocolo','LIKE','%' . $criterio . '%');
        })->paginate(25);
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

    public function getCountAgendamentoPendenteByCpfDayHour($dia, $hora, $cpf)
    {
        return Agendamento::where('dia', $dia)
            ->where('hora', $hora)
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

    public function getAgendamentoPendenteByMesRegional($idregional)
    {
        return Agendamento::select('dia', DB::raw('count(1) as total'))        
            ->whereBetween('dia',[date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+1 month'))])
            ->where('idregional', $idregional)
            ->whereNull('status')
            ->groupBy('dia')
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
   
    public function getToTableFilter($mindia, $maxdia, $regional, $status, $servico)
    {
        $resultados = Agendamento::whereBetween('dia',[$mindia,$maxdia]);

        if(!empty($regional)) {
            $resultados->where('idregional', $regional);
        }

        if(!empty($status)) {
            $resultados->where('status', $status);
        }

        if(!empty($servico)) {
            $resultados->where('tiposervico', $servico);
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

    public function getCountAllAgendamentos()
    {
        return Agendamento::all()->count();
    }

    // private function countPlantaoJuridicoByRegionalAndDia($regional, $dia)
    // {
    //     return Agendamento::where('idregional', $regional)
    //         ->where('tiposervico', 'LIKE', Agendamento::SERVICOS_PLANTAO_JURIDICO.'%')
    //         ->whereNull('status')
    //         ->where('dia', $dia)
    //         ->count();
    // }

    // private function getPlantaoJuridicoByRegionalAndDia($regional, $dia)
    // {
    //     return Agendamento::select('hora')
    //         ->where('idregional', $regional)
    //         ->where('tiposervico', 'LIKE', Agendamento::SERVICOS_PLANTAO_JURIDICO.'%')
    //         ->whereNull('status')
    //         ->where('dia', $dia)
    //         ->get();
    // }

    // private function getMesRegionalPlantaoJuridico($regional)
    // {
    //     $rodadaPJ = [
    //         4 => '02',
    //         5 => '02',
    //         8 => '02',
    //         10 => '02',
    //         12 => '02'
    //     ];

    //     return $rodadaPJ[$regional];
    // }

    // public function getPlantaoJuridicoByCPF($cpf, $regional)
    // {
    //     $mes = $this->getMesRegionalPlantaoJuridico($regional);

    //     return Agendamento::where('cpf', $cpf)
    //         ->where('idregional', $regional)
    //         ->where('tiposervico', 'LIKE', Agendamento::SERVICOS_PLANTAO_JURIDICO.'%')
    //         ->whereNull('status')
    //         ->where('dia', 'LIKE', date('Y').'-'.$mes.'-%')
    //         ->count();
    // }

    // public function estaLotadoPlantaoJuridico($regional, $dia)
    // {
    //     $total = $this->countPlantaoJuridicoByRegionalAndDia($regional, $dia);

    //     // Em Janeiro de 2022
    //     $regionaisTotalRCs = [
    //         4 => [
    //             '2022-02-09' => 10,
    //             '2022-02-10' => 12,
    //             '2022-02-11' => 12,
    //         ],
    //         5 => [
    //             '2022-02-07' => 10,
    //             '2022-02-08' => 12,
    //         ],
    //         8 => [
    //             '2022-02-07' => 6,
    //             '2022-02-08' => 12,
    //         ],
    //         10 => [
    //             '2022-02-10' => 12,
    //             '2022-02-11' => 4,
    //         ],
    //         12 => [
    //             '2022-02-09' => 12,
    //             '2022-02-10' => 12,
    //         ]
    //     ];

    //     if(!isset($regionaisTotalRCs[$regional][$dia]))
    //         return null;

    //     return $regionaisTotalRCs[$regional][$dia] == $total ? true : false;
    // }

    // public function getHorasPlantaoJuridicoByRegionalAndDia($regional, $dia)
    // {
    //     $horasCheias = $this->getPlantaoJuridicoByRegionalAndDia($regional, $dia);
    //     $horasTotais = explode(';', $this->diasHorasPlantaoJuridico()[$regional][$dia]);

    //     foreach($horasCheias as $hora)
    //     {
    //         if(isset($horasTotais[array_search($hora->hora, $horasTotais)]))
    //             unset($horasTotais[array_search($hora->hora, $horasTotais)]);
    //     }

    //     return $horasTotais;
    // }

    // public function diasHorasPlantaoJuridico()
    // {
    //     // Em Janeiro de 2022
    //     return $regionaisDiasHoras = [
    //         4 => [
    //             '2022-02-09' => '11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30',
    //             '2022-02-10' => '10:00;10:30;11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30',
    //             '2022-02-11' => '10:00;10:30;11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30'
    //         ],
    //         5 => [
    //             '2022-02-07' => '11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30',
    //             '2022-02-08' => '10:00;10:30;11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30'
    //         ],
    //         8 => [
    //             '2022-02-07' => '14:00;14:30;15:00;15:30;16:00;16:30',
    //             '2022-02-08' => '10:00;10:30;11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30'
    //         ],
    //         10 => [
    //             '2022-02-10' => '10:00;10:30;11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30',
    //             '2022-02-11' => '10:00;10:30;11:00;11:30'
    //         ],
    //         12 => [
    //             '2022-02-09' => '10:00;10:30;11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30',
    //             '2022-02-10' => '10:00;10:30;11:00;11:30;12:00;12:30;14:00;14:30;15:00;15:30;16:00;16:30'
    //         ]
    //     ];
    // }
}