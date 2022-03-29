<?php

namespace App;

use App\Repositories\AgendamentoBloqueioRepository;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Regional extends Model
{
    protected $table = 'regionais';
    protected $primaryKey = 'idregional';
    protected $guarded = [];
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany('App\User', 'idregional')->withTrashed();
    }

    public function noticias()
    {
        return $this->hasMany('App\Noticia', 'idregional');
    }

    public function agendamentos()
    {
        return $this->hasMany('App\Agendamento', 'idregional');
    }

    public function agendamentosBloqueios()
    {
        return $this->hasMany('App\AgendamentoBloqueio', 'idregional');
    }

    public function plantaoJuridico()
    {
        return $this->hasOne('App\PlantaoJuridico', 'idregional');
    }

    public function horariosAge()
    {
        if(isset($this->horariosage))
            return explode(',', $this->horariosage);

        return [];
    }

    public function horariosDisponiveis($dia)
    {
        $horas = $this->horariosAge();
        $bloqueios = (new AgendamentoBloqueioRepository)->getByRegionalAndDay($this->idregional, $dia);
        if($bloqueios && $horas) {
            foreach($bloqueios as $bloqueio) {
                foreach($horas as $key => $hora) {
                    if($hora >= $bloqueio->horainicio && $hora <= $bloqueio->horatermino) {
                        unset($horas[$key]);
                    }
                }
            }
        }
        return $horas;
    }

    // tentar ajustar para o periodo mensal
    private function getHorariosComBloqueio($dia)
    {
        $agendamentosBloqueios = $this->agendamentosBloqueios()
        ->where('diatermino', '>=', $dia)
        ->orWhere(function($query) {
            $query->where('idregional', $this->idregional)
            ->whereNull('diatermino');
        })
        ->get();

        $resultado = [
            'horarios' => $this->horariosAge(),
            'atendentes' => null
        ];

        if($agendamentosBloqueios->isNotEmpty())
            foreach($agendamentosBloqueios as $bloqueio)
            {
                $inicialBloqueio = Carbon::parse($bloqueio->diainicio);
                $finalBloqueio = isset($bloqueio->diatermino) ? Carbon::parse($bloqueio->diatermino) : null;
                $dia = Carbon::parse($dia);

                if($inicialBloqueio->lte($dia) && (!isset($finalBloqueio) || $finalBloqueio->gte($dia)))
                {
                    $horariosBloqueios = explode(',', $bloqueio->horarios);
                    foreach($horariosBloqueios as $horario)
                        if($bloqueio->qtd_atendentes == 0)
                            unset($resultado['horarios'][array_search($horario, $resultado['horarios'])]);
                        else
                            $resultado['atendentes'] = [
                                $horario => $bloqueio->qtd_atendentes
                            ];
                }
            }

        return $resultado;
    }

    public function getAgendadosPorPeriodo($inicio, $final)
    {
        return $this->agendamentos()
            ->select('dia', 'hora')
            ->where('tiposervico', 'NOT LIKE', 'Plantão Jurídico%')
            ->whereNull('status')
            ->whereBetween('dia', [$inicio, $final])
            ->orderby('dia')
            ->orderby('hora')
            ->get()
            ->groupBy([
                'dia',
                function ($item) {
                    return $item['hora'];
                },
            ], $preserveKeys = false);
    }

    public function getDiasSeLotado($agendados)
    {
        $diasLotados = array();

        foreach($agendados as $key => $agendado)
        {
            $dia = Carbon::parse($key);
            $horarios = $this->removeHorariosSeLotado($agendados[$dia->format('Y-m-d')], $dia->format('Y-m-d'));
            if(empty($horarios))
                array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
        }

        return $diasLotados;
    }

    public function removeHorariosSeLotado($agendado, $dia)
    {
        $resultado = $this->getHorariosComBloqueio($dia);
        $horarios = $resultado['horarios'];
        $atendentes = isset($resultado['atendentes']) ? $resultado['atendentes'] : null;

        if($agendado->isNotEmpty())
            foreach($agendado as $hora => $value)
                if(isset($atendentes[$hora]) && ($atendentes[$hora] == $value->count()))
                    unset($horarios[array_search($hora, $horarios)]);
        
        return $horarios;
    }
}
