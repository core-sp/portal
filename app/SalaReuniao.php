<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public static function horasManha()
    {
        return array_slice(todasHoras(), 0, 6);
    }

    public static function horasTarde()
    {
        return array_slice(todasHoras(), 9, 6);
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

    private function getPeriodosCombloqueio($bloqueios, $dia, $tipo)
    {
        $dia = Carbon::parse($dia);

        if($dia->isWeekend())
            return [];

        $periodos = array();
        $manha = array();
        $tarde = array();
        $horarios = array_merge($this->getHorariosManha($tipo), $this->getHorariosTarde($tipo));

        if($bloqueios->isNotEmpty())
            foreach($bloqueios as $bloqueio)
                $horarios = $bloqueio->getHorarios($horarios, $dia);

        foreach($horarios as $hora)
        {
            if($hora < '12:30'){
                array_push($periodos, 'manha');
                array_push($manha, $hora);
            }else{
                array_push($periodos, 'tarde');
                array_push($tarde, $hora);
            }
        }

        if($tipo == 'reuniao')
            $this->horarios_reuniao = json_encode(['manha' => $manha, 'tarde' => $tarde], JSON_FORCE_OBJECT);
        else
            $this->horarios_coworking = json_encode(['manha' => $manha, 'tarde' => $tarde], JSON_FORCE_OBJECT);

        return array_unique($periodos);
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

    public function bloqueios()
    {
    	return $this->hasMany('App\SalaReuniaoBloqueio', 'sala_reuniao_id');
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

    public function getHorariosManha($tipo)
    {
        if($tipo == 'reuniao')
            return json_decode($this->horarios_reuniao, true)['manha'];
        if($tipo == 'coworking')
            return json_decode($this->horarios_coworking, true)['manha'];
        return array();
    }

    public function getHorariosTarde($tipo)
    {
        if($tipo == 'reuniao')
            return json_decode($this->horarios_reuniao, true)['tarde'];
        if($tipo == 'coworking')
            return json_decode($this->horarios_coworking, true)['tarde'];
        return array();
    }

    public function getTodasHoras()
    {
        $manha = array_merge(json_decode($this->horarios_reuniao, true)['manha'], json_decode($this->horarios_coworking, true)['manha']);
        $tarde = array_merge(json_decode($this->horarios_reuniao, true)['tarde'], json_decode($this->horarios_coworking, true)['tarde']);
        $horas = array_unique(array_merge($manha, $tarde));
        sort($horas, SORT_NATURAL);

        return $horas;
    }

    public function getItens($tipo)
    {
        if($tipo == 'reuniao')
            return json_decode($this->itens_reuniao, true);
        if($tipo == 'coworking')
            return json_decode($this->itens_coworking, true);
        return array();
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
                    $all[$temp_k] = self::itensHTML()[$key] . $final[$temp_k];
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
                    $all[$temp_k] = self::itensHTML()[$key] . $final[$temp_k];
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

    public function verificaAlteracaoItens($reuniao, $coworking, $participantes)
    {
        $itens['reuniao'] = array();
        $itens['coworking'] = array();
        $gerente = null;

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

        if(!empty($itens['reuniao']) || !empty($itens['coworking'])){
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

    public function getDiasSeLotado($tipo)
    {
        if(!$this->isAtivo($tipo) || !in_array($tipo, ['reuniao', 'coworking']))
            return null;

        $diasLotados = array();
        $diaslotadosBloqueio = array();
        $bloqueios = $this->bloqueios;
        $agendados = $this->agendamentosSala()
            ->select('dia', DB::raw('count(*) as total'))
            ->where('tipo_sala', $tipo)
            ->whereNull('status')
            ->whereBetween('dia', [Carbon::tomorrow()->format('Y-m-d'), Carbon::today()->addMonth()->format('Y-m-d')])
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $dia = Carbon::tomorrow();
        for($dia; $dia->lte(Carbon::today()->addMonth()); $dia->addDay())
        {
            $periodosTotal = $this->getPeriodosComBloqueio($bloqueios, $dia->format('Y-m-d'), $tipo);
            if(sizeof($periodosTotal) == 0)
            {
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
                array_push($diaslotadosBloqueio, $dia->format('Y-m-d'));
            }
        }

        foreach($agendados as $agendado)
        {
            $dia = Carbon::parse($agendado->dia);
            if(!array_search($dia->format('Y-m-d'), $diaslotadosBloqueio))
            {
                $periodosTotal = $this->getPeriodosComBloqueio($bloqueios, $dia->format('Y-m-d'), $tipo);
                $total = $tipo == 'reuniao' ? sizeof($periodosTotal) : sizeof($periodosTotal) * $this->participantes_coworking;
                if($agendado->total >= $total)
                    array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
            }
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($tipo, $dia)
    {
        if(!$this->isAtivo($tipo) || !in_array($tipo, ['reuniao', 'coworking']))
            return array();

        $bloqueios = $this->bloqueios;
        $periodos = $this->getPeriodosComBloqueio($bloqueios, $dia, $tipo);
        $final_periodos = $periodos;

        if(sizeof($periodos) == 0)
            return $periodos;

        $agendado = $this->agendamentosSala()
            ->select('periodo', DB::raw('count(*) as total'))
            ->where('tipo_sala', $tipo)
            ->whereNull('status')
            ->whereDate('dia', $dia)
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        if($agendado->isNotEmpty())
        {
            $soma = 0;
            foreach($agendado as $value)
            {
                $soma += $value->total;
                switch ($tipo) {
                    case 'reuniao':
                        if($value->total >= 1)
                            unset($periodos[array_search($value->periodo, $periodos)]);
                        if($soma >= count($final_periodos))
                            $periodos = array();
                        break;
                    case 'coworking':
                        if($value->total >= $this->participantes_coworking)
                            unset($periodos[array_search($value->periodo, $periodos)]);
                        if($soma >= (count($final_periodos) * $this->participantes_coworking))
                            $periodos = array();
                        break;
                }
            }
        }
        
        return $periodos;
    }
}
