<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendamentoSala extends Model
{
    use SoftDeletes;
    
    protected $table = 'agendamentos_salas';
    protected $guarded = [];

    const STATUS_CANCELADO = 'Cancelado';
    const STATUS_COMPARECEU = 'Compareceu';
    const STATUS_ENVIADA = 'Justificativa Enviada';
    const STATUS_NAO_COMPARECEU = 'Não Compareceu';
    const STATUS_JUSTIFICADO = 'Não Compareceu Justificado';

    public static function status()
    {
        return [
            self::STATUS_CANCELADO,
            self::STATUS_COMPARECEU,
            self::STATUS_ENVIADA,
            self::STATUS_NAO_COMPARECEU,
            self::STATUS_JUSTIFICADO,
        ];
    }

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

    public static function getAgendadoParticipanteByCpf($cpf)
    {
    	return self::where('participantes', 'LIKE', '%"'. apenasNumeros($cpf) .'"%')
        ->whereNull('status')
        ->whereBetween('dia', [Carbon::tomorrow()->format('Y-m-d'), Carbon::today()->addMonth()->format('Y-m-d')])
        ->orderBy('dia')
        ->orderBy('periodo')
        ->get();
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

    public function isReuniao()
    {
    	return $this->tipo_sala == 'reuniao';
    }

    public function getTipoSala()
    {
    	return $this->isReuniao() ? 'Reunião' : 'Coworking';
    }

    public function getTipoSalaHTML()
    {
    	return $this->isReuniao() ? '<i class="fas fa-briefcase"></i> Reunião' : '<i class="fas fa-laptop"></i> Coworking';
    }

    public function getPeriodo()
    {
    	return $this->periodo == 'manha' ? 'Manhã' : 'Tarde';
    }

    public function getParticipantes()
    {
        return $this->isReuniao() ? json_decode($this->participantes, true) : array();
    }

    public function getParticipantesComTotal()
    {
        $total = $this->sala->participantes_reuniao - 1;
        $final = $this->getParticipantes();
        $atual = $total - count($final);
        if(($this->isReuniao()) && (count($final) < $this->sala->participantes_reuniao))
            for($i = 1; $i <= $atual; $i++)    
                $final[$i] = '';
        return $final;
    }

    public function podeEditarParticipantes()
    {
        return $this->isReuniao() && (now()->format('Y-m-d') < $this->dia) && !isset($this->status);
    }

    public function podeCancelar()
    {
        return (now()->format('Y-m-d') < $this->dia) && !isset($this->status);
    }

    public function podeJustificar()
    {
        return (now()->format('Y-m-d') <= $this->getDataLimiteJustificar(false)) && (now()->format('Y-m-d') >= $this->dia) && !isset($this->status);
    }

    public function podeAtualizarStatus()
    {
        return (now()->format('Y-m-d') >= $this->dia) && (!isset($this->status) || $this->justificativaEnviada());
    }

    public function getDataLimiteJustificar($comBarra = true)
    {
        $dia = Carbon::parse($this->dia)->addDays(2);

        if($comBarra)
            return $dia->format('d/m/Y');
        return $dia->format('Y-m-d');
    }

    public static function participantesVetados($dia, $periodo, $cpfs, $id = null)
    {
        $vetados = array();

        $agendados = self::when(isset($id), function($query) use($id){
            return $query->where('id', '!=', $id);
        })
        ->whereNull('status')
        ->where('dia', $dia)
        ->where('periodo', $periodo)
        ->get();        
        
        foreach ($agendados as $key => $value) {
            if($value->representante->tipoPessoa() == 'PF')
                $vetados = array_merge($vetados, array_intersect($cpfs, [apenasNumeros($value->representante->cpf_cnpj)]));
            if($value->isReuniao()){
                $participantes = array_keys(json_decode($value->participantes, true));
                $vetados = array_merge($vetados, array_intersect($cpfs, $participantes));
            }
        }

        return array_unique($vetados);
    }

    public function justificativaEnviada()
    {
        return $this->status == self::STATUS_ENVIADA;
    }

    public function getStatusHTML()
    {
        $status = [
            self::STATUS_CANCELADO => '<strong>'.self::STATUS_CANCELADO.'</strong>',
            self::STATUS_COMPARECEU => '<span class="text-success font-weight-bold"><i class="fas fa-check checkIcone"></i>&nbsp;&nbsp;'.self::STATUS_COMPARECEU.'</span>',
            self::STATUS_ENVIADA => '<span class="text-primary font-weight-bold">'.self::STATUS_ENVIADA.'</span>',
            self::STATUS_NAO_COMPARECEU => '<span class="text-danger font-weight-bold">'.self::STATUS_NAO_COMPARECEU.'</span>',
            self::STATUS_JUSTIFICADO => '<span class="text-secondary font-weight-bold"><i class="fas fa-marker"></i>&nbsp;&nbsp;'.self::STATUS_JUSTIFICADO.'</span>',
        ];

        return isset($this->status) ? $status[$this->status] : '';
    }

    public function getBtnStatusCompareceu()
    {
        if(isset($this->status))
            return '';
            
        if($this->podeAtualizarStatus())
        {
            $default = '<form method="POST" action="'.route('sala.reuniao.agendados.update', ['id' => $this->id, 'acao' => 'confirma']).'" class="d-inline">';
            $default .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $default .= '<input type="hidden" name="_method" value="PUT" id="method" />';
            $default .= '<button type="submit" name="status" class="btn btn-sm btn-success" value="'.self::STATUS_COMPARECEU.'">Confirmar</button>';
            $default .= '</form>';
            return $default;
        }
        return '';
    }
}
