<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class SalaReuniao extends Model
{
    protected $table = 'salas_reunioes';
    protected $guarded = [];

    const ITEM_TV = 'TV _ polegadas com entrada HDMI';
    const ITEM_CABO = 'Cabo de _ metro(s) para conexão HDMI';
    const ITEM_AR = 'Ar-condicionado';
    const ITEM_MESA = 'Mesa com _ cadeira(s)';
    const ITEM_AGUA = 'Água';
    const ITEM_CAFE = 'Café';
    const ITEM_WIFI = 'Wi-Fi';

    public static function getFormatHorariosHTML($horarios = array())
    {
        foreach($horarios as $key => $hora)
        {
            $hora = str_replace(' - ', ' até ', $hora);
            $horarios[$key] = in_array($key, ['manha', 'tarde']) ? 'Período todo: ' . $hora : $hora;
        }

        return implode('<br>', $horarios);
    }

    public static function periodoManha()
    {
        return array_slice(todasHoras(), 4, 7);
    }

    public static function periodoTarde()
    {
        $todasHoras = todasHoras();
        array_push($todasHoras, '18:00');

        return array_slice($todasHoras, 12, 7);
    }

    public static function itens()
    {
        return [
            'tv' => self::ITEM_TV,
            'cabo' => self::ITEM_CABO,
            'ar' => self::ITEM_AR,
            'mesa' => self::ITEM_MESA,
            'agua' => self::ITEM_AGUA,
            'cafe' => self::ITEM_CAFE,
            'wifi' => self::ITEM_WIFI,
        ];
    }

    public static function itensHTML()
    {
        return [
            'tv' => '<i class="fas fa-tv text-primary"></i> ',
            'cabo' => '<i class="fas fa-plug text-primary"></i> ',
            'ar' => '<i class="far fa-snowflake text-primary"></i> ',
            'mesa' => '<i class="fas fa-chair text-primary"></i> ',
            'agua' => '<i class="fas fa-coffee text-primary"></i> ',
            'cafe' => '<i class="fas fa-mug-hot text-primary"></i> ',
            'wifi' => '<i class="fas fa-wifi text-primary"></i> ',
        ];
    }

    public static function itensCoworking()
    {
        $itens = [
            'ar' => self::ITEM_AR,
            'mesa' => self::ITEM_MESA,
            'agua' => self::ITEM_AGUA,
            'cafe' => self::ITEM_CAFE,
            'wifi' => self::ITEM_WIFI,
        ];

        foreach($itens as $key => $val){
            if(strpos($val, '_') !== false)
                $itens[$key] = str_replace('_', '1', $val);
        }

        return $itens;
    }

    private function getPeriodosCombloqueio($bloqueios, $dia, $horarios)
    {
        $dia = Carbon::parse($dia);

        if($dia->isWeekend())
            return [];

        if($bloqueios->isNotEmpty())
            foreach($bloqueios as $bloqueio)
                $horarios = $bloqueio->getHorarios($horarios, $dia);

        return $horarios;
    }

    private function confereLotadoDiaPorTipo($tipo, $agendado, $horarios)
    {
        $total_periodos = 0;

        $total_periodos = count(Arr::where($horarios, function ($value, $key) {
            return $value <= $this->hora_limite_final_manha;
        })) > 0 ? $total_periodos + 1 : $total_periodos;

        $total_periodos = count(Arr::where($horarios, function ($value, $key) {
            return $value > $this->hora_limite_final_manha;
        })) > 0 ? $total_periodos + 1 : $total_periodos;

        $qtd_periodo_todo = $tipo == 'reuniao' ? $total_periodos : $this->participantes_coworking * $total_periodos;
        $qtd_por_hora = $tipo == 'reuniao' ? count($horarios) : $this->participantes_coworking * count($horarios);

        $periodo_todo = $agendado->where('periodo_todo', 1)->first();
        $total = $agendado->sum('total');

        if((isset($periodo_todo) && ($periodo_todo->total >= $qtd_periodo_todo)) || ($total >= $qtd_por_hora))
            return true;

        return false;
    }

    private function removeHorasPorHora($periodo, $horarios_agendar)
    {
        $periodo = explode(' - ', $periodo);
        $duracao = Carbon::parse($periodo[0])->diffInMinutes(Carbon::parse($periodo[1]));
        
        $horarios_agendar = Arr::except($horarios_agendar, 
            array_values(array_keys(Arr::where($horarios_agendar, function ($value, $key) use($periodo, $duracao) {
                $temp = explode(' - ', $value);
                $inicio_temp = Carbon::parse($temp[0]);
                $periodo_inicio = Carbon::parse($periodo[0]);
                $duracao_temp = $periodo_inicio->diffInMinutes($inicio_temp);
                $periodo_inicio->addMinute();
                return $periodo_inicio->between($temp[0], $temp[1]) || ($periodo_inicio->lt($inicio_temp) && ($duracao_temp < $duracao));
            }))));

        return $horarios_agendar;
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function agendamentosSala()
    {
    	return $this->hasMany('App\AgendamentoSala', 'sala_reuniao_id');
    }

    // Retorna somente bloqueios com data final válida
    public function bloqueios()
    {
    	return $this->hasMany('App\SalaReuniaoBloqueio', 'sala_reuniao_id')
            ->where('dataFinal', '>=', date('Y-m-d'))
            ->orWhere(function($query) {
                $query->where('sala_reuniao_id', $this->id)
                ->whereNull('dataFinal');
            });
    }

    public function isAtivo($tipo)
    {
        if($tipo == 'reuniao')
            return $this->participantes_reuniao > 0;
        if($tipo == 'coworking')
            return $this->participantes_coworking > 0;
        return false;
    }

    public function getParticipantesAgendar($tipo)
    {
        if($tipo == 'reuniao')
            return $this->participantes_reuniao - 1;
        return 0;
    }

    public function getHorarios($tipo)
    {
        if($tipo == 'reuniao')
            return explode(',', $this->horarios_reuniao);
        if($tipo == 'coworking')
            return explode(',', $this->horarios_coworking);
        return array();
    }

    public function getTodasHoras()
    {
        $final = array_unique(array_merge(explode(',', $this->horarios_reuniao), explode(',', $this->horarios_coworking)));
        sort($final);

        return $final;
    }

    public function getItens($tipo)
    {
        if($tipo == 'reuniao')
            return json_decode($this->itens_reuniao, true);
        if($tipo == 'coworking')
            return json_decode($this->itens_coworking, true);
        return array();
    }

    public function horaAlmoco()
    {
        return $this->hora_limite_final_manha;
    }

    public function horaFimExpediente()
    {
        return $this->hora_limite_final_tarde;
    }

    public function getItensHtml($tipo)
    {
        $all = null;
        if($tipo == 'reuniao')
        {
            if(strlen($this->itens_reuniao) < 5)
                return null;

            $all = json_decode($this->itens_reuniao, true);
            $final = $all;
            $original = self::itens();
            foreach($all as $key_ => $val_)
                $all[$key_] = preg_replace('/[0-9,]/', '', $val_);
            foreach($original as $key => $val){
                $temp = str_replace('_', '', $val);
                $temp_k = array_search($temp, $all, true);
                if($temp_k !== false)
                    $all[$temp_k] = '<span class="text-nowrap">'.self::itensHTML()[$key] . $final[$temp_k].'</span>';
            }
        }elseif($tipo == 'coworking'){
            if(strlen($this->itens_coworking) < 5)
                return null;
            
            $all = json_decode($this->itens_coworking, true);
            $final = $all;
            $original = self::itensCoworking();
            foreach($original as $key => $val){
                $temp = str_replace('_', '', $val);
                $temp_k = array_search($temp, $all, true);
                if($temp_k !== false)
                    $all[$temp_k] = '<span class="text-nowrap">'.self::itensHTML()[$key] . $final[$temp_k].'</span>';
            }
        }
        
        return $all;
    }

    public function getItensOriginaisReuniao()
    {
        if(strlen($this->itens_reuniao) < 5)
            return self::itens();

        $all = json_decode($this->itens_reuniao, true);
        $original = self::itens();
        foreach($all as $key_ => $val_)
            $all[$key_] = preg_replace('/[0-9,]/', '', $val_);
        foreach($original as $key => $val){
            $temp = str_replace('_', '', $val);
            if(in_array($temp, $all))
                unset($original[$key]);
        }
        
        return $original;
    }

    public function getItensOriginaisCoworking()
    {
        if(strlen($this->itens_coworking) < 5)
            return self::itensCoworking();
            
        $all = json_decode($this->itens_coworking, true);
        $original = self::itensCoworking();
        foreach($original as $key => $val){
            $temp = str_replace('_', '', $val);
            if(in_array($temp, $all))
                unset($original[$key]);
        }
        
        return $original;
    }

    public function verificaAlteracaoItens($reuniao, $coworking, $participantes, $periodos)
    {
        $itens['reuniao'] = array();
        $itens['coworking'] = array();
        $gerente = null;

        if($this->wasChanged('hora_limite_final_manha'))
            $itens['final_manha'] = $periodos['final_manha'];

        if($this->wasChanged('hora_limite_final_tarde'))
            $itens['final_tarde'] = $periodos['final_tarde'];

        if($this->wasChanged('itens_reuniao')){
            $itens['reuniao'] = array_diff($reuniao, $this->getItens('reuniao'));
            $itens['reuniao'] = array_merge($itens['reuniao'], array_diff($this->getItens('reuniao'), $reuniao));
        }
        if($this->wasChanged('participantes_reuniao'))
            $itens['reuniao']['participantes'] = $participantes['reuniao'];
            
        if($this->wasChanged('itens_coworking')){
            $itens['coworking'] = array_diff($coworking, $this->getItens('coworking'));
            $itens['coworking'] = array_merge($itens['coworking'], array_diff($this->getItens('coworking'), $coworking));
        }
        if($this->wasChanged('participantes_coworking'))
            $itens['coworking']['participantes'] = $participantes['coworking'];

        if(!empty($itens['reuniao']) || !empty($itens['coworking']) || (isset($itens['final_manha']) || isset($itens['final_tarde']))){
            $gerente = $this->regional->users()
            ->when(in_array($this->idregional, [1, 14]), function ($query){
                return $query->select('nome', 'email')->where('idusuario', 39);
            }, function ($query) {
                return $query->select('nome', 'email')->where('idperfil', 21)->where('email', 'LIKE', 'ger%');
            })->first();
        }

        return [
            'gerente' => $gerente,
            'itens' => $itens,
        ];
    }

    public function formatarHorariosAgendamento($horarios = array())
    {
        if(empty($horarios) || (isset($horarios[0]) && (strlen($horarios[0]) == 0)))
            return array();

        $formatado = array();
        $separador = ' - ';

        $final['manha'] = Arr::where($horarios, function ($value, $key) {
            return $value <= $this->horaAlmoco();
        });

        $final['tarde'] = Arr::where($horarios, function ($value, $key) {
            return $value > $this->horaAlmoco();
        });

        if((count($final['manha']) > 0) || (count($final['tarde']) > 0))
        {
            foreach($final as $periodo => $arrayHoras)
            {
                if(count($arrayHoras) == 0)
                    continue;

                $hora_final = $periodo == 'manha' ? $this->horaAlmoco() : $this->horaFimExpediente();
                $periodo_todo = $arrayHoras[array_keys($arrayHoras)[0]] . $separador . $hora_final;
                foreach($arrayHoras as $key => $val)
                {
                    if(count($arrayHoras) == 1)
                        break;
                    $seguinte = $key + 1;
                    $formatado[$val] = $val != last($arrayHoras) && isset($arrayHoras[$seguinte]) ? 
                    $val . $separador . $arrayHoras[$seguinte] : $val . $separador . $hora_final;
                }
                $formatado[$periodo] = $periodo_todo;
            }
        }

        return $formatado;
    }

    public function formatarHorariosAgendamentoHTML($tipo)
    {
        if(!in_array($tipo, ['reuniao', 'coworking']))
            return array();

        $horarios = $this->formatarHorariosAgendamento($this->getHorarios($tipo));

        return self::getFormatHorariosHTML($horarios);
    }

    public function getDiasSeLotado($tipo)
    {
        if(!$this->isAtivo($tipo) || !in_array($tipo, ['reuniao', 'coworking']))
            return null;

        $horarios = $this->getHorarios($tipo);
        $diasLotados = array();
        $diaslotadosBloqueio = array();
        $bloqueios = $this->bloqueios;
        $agendados = $this->agendamentosSala()
            ->select('dia', 'periodo_todo', DB::raw('count(*) as total'))
            ->where('tipo_sala', $tipo)
            ->whereNull('status')
            ->whereBetween('dia', [Carbon::tomorrow()->format('Y-m-d'), Carbon::today()->addMonth()->format('Y-m-d')])
            ->groupBy('dia')
            ->groupBy('periodo_todo')
            ->orderBy('dia')
            ->orderBy('periodo_todo', 'DESC')
            ->get();

        $dia = Carbon::tomorrow();

        for($dia; $dia->lte(Carbon::today()->addMonth()); $dia->addDay())
        {
            $periodosTotal = $this->getPeriodosComBloqueio($bloqueios, $dia->format('Y-m-d'), $horarios);
            if(sizeof($periodosTotal) == 0)
            {
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
                array_push($diaslotadosBloqueio, $dia->format('Y-m-d'));
            }
        }

        $agendados = $agendados->groupBy('dia');
        foreach($agendados as $agendado)
        {
            $dia = Carbon::parse($agendado->get(0)->dia);
            if(!array_search($dia->format('Y-m-d'), $diaslotadosBloqueio))
            {
                $periodosTotal = $this->getPeriodosComBloqueio($bloqueios, $dia->format('Y-m-d'), $horarios);
                if($this->confereLotadoDiaPorTipo($tipo, $agendado, $periodosTotal))
                    array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
            }
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($tipo, $dia)
    {
        if(!$this->isAtivo($tipo) || !in_array($tipo, ['reuniao', 'coworking']))
            return array();

        $horarios = $this->getHorarios($tipo);
        $bloqueios = $this->bloqueios;
        $horarios = $this->getPeriodosComBloqueio($bloqueios, $dia, $horarios);
        $horarios_agendar = $this->formatarHorariosAgendamento($horarios);

        if(sizeof($horarios) == 0)
            return $horarios_agendar;

        $agendados = $this->agendamentosSala()
            ->select('periodo', 'periodo_todo', DB::raw('count(*) as total'))
            ->where('tipo_sala', $tipo)
            ->whereNull('status')
            ->whereDate('dia', $dia)
            ->groupBy('periodo')
            ->groupBy('periodo_todo')
            ->orderBy('periodo_todo', 'DESC')
            ->get();

        if($agendados->isNotEmpty())
        {
            foreach($agendados as $value)
            {
                if(($tipo == 'reuniao') || (($tipo == 'coworking') && ($value->total >= $this->participantes_coworking)))
                    $horarios_agendar = $this->removeHorasPorHora($value->periodo, $horarios_agendar);
            }
        }
        
        return $horarios_agendar;
    }
}
