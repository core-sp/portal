<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AgendamentoSala extends Model
{
    protected $table = 'agendamentos_salas';
    protected $guarded = [];

    const STATUS_CANCELADO = 'Cancelado';
    const STATUS_COMPARECEU = 'Compareceu';
    const STATUS_PENDENTE = 'Aguardando Justificativa';
    const STATUS_ENVIADA = 'Justificativa Enviada';
    const STATUS_NAO_COMPARECEU = 'Não Compareceu';
    const STATUS_JUSTIFICADO = 'Não Compareceu Justificado';

    public function sala()
    {
    	return $this->belongsTo('App\SalaReuniao', 'sala_reuniao_id');
    }

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public static function getProtocolo()
    {
        // Gera a HASH (protocolo) aleatória
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVXZ0123456789';
        do {
            $protocoloGerado = substr(str_shuffle($characters), 0, 8);
            $protocoloGerado = 'RC-AGE-'.$protocoloGerado;
            $countProtocolo = self::where('protocolo', $protocoloGerado)->count();
        } while($countProtocolo != 0);

        return $protocoloGerado;
    }

    public function getTipoSala()
    {
    	return $this->tipo_sala == 'reuniao' ? 'Reunião' : 'Coworking';
    }

    public function getPeriodo()
    {
    	return $this->periodo == 'manha' ? 'Manhã' : 'Tarde';
    }

    public function getParticipantes()
    {
        return $this->tipo_sala == 'reuniao' ? json_decode($this->participantes, true) : array();
    }

    public function getParticipantesComTotal()
    {
        $total = $this->sala->participantes_reuniao - 1;
        $final = $this->getParticipantes();
        $atual = $total - count($final);
        if(($this->tipo_sala == 'reuniao') && (count($final) < $this->sala->participantes_reuniao))
            for($i = 1; $i <= $atual; $i++)    
                $final[$i] = '';
        return $final;
    }

    public function podeEditarParticipantes()
    {
        return ($this->tipo_sala == 'reuniao') && (now() < $this->dia) && !isset($this->status);
    }

    public function podeCancelar()
    {
        return (now() < $this->dia) && !isset($this->status);
    }

    public function podeJustificar()
    {
        $dia = Carbon::createFromFormat('Y-m-d', $this->dia);

        return (now() <= $dia->addDays(2)) && (now() >= $this->dia) && (!isset($this->status) || ($this->status == self::STATUS_PENDENTE));
    }

    public function getDataLimiteJustificar()
    {
        $dia = Carbon::createFromFormat('Y-m-d', $this->dia);

        return $dia->addDays(2)->format('d/m/Y');
    }
}
