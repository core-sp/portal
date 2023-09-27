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

    public function suspenso()
    {
    	return $this->hasOne('App\SuspensaoExcecao', 'agendamento_sala_id');
    }

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'agendamento_sala_id');
    }

    public static function getAgendadoParticipanteByCpf($cpf)
    {
    	return self::where('participantes', 'LIKE', '%"'. apenasNumeros($cpf) .'"%')
        ->whereNull('status')
        ->whereBetween('dia', [Carbon::today()->format('Y-m-d'), Carbon::today()->addMonth()->format('Y-m-d')])
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

    public function temAnexo()
    {
    	return isset($this->anexo) && (strpos($this->anexo, '[removido]') === false);
    }

    public function anexoRemovido()
    {
    	return isset($this->anexo) && (strpos($this->anexo, '[removido]') !== false);
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
    	return str_replace(' - ', ' até ', $this->periodo);
    }

    public function getParticipantes()
    {
        return $this->isReuniao() ? json_decode($this->participantes, true) : array();
    }

    public function getParticipantesComTotal()
    {
        $final = $this->getParticipantes();
        $total = $this->sala->participantes_reuniao == 0 ? count($final) : $this->sala->participantes_reuniao - 1;
        $atual = $total - count($final);
        if((($this->isReuniao()) && (count($final) < $this->sala->participantes_reuniao)) || !$this->sala->isAtivo('reuniao'))
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

    public function inicioDoPeriodo()
    {
        return explode(' - ', $this->periodo)[0];
    }

    public function fimDoPeriodo()
    {
        return explode(' - ', $this->periodo)[1];
    }

    public static function participantesVetados($dia, $periodo, $cpfs, $periodoTodo = true, $id = null)
    {
        $vetados = array();
        $periodo = explode(' - ', $periodo);

        $agendados = self::when(isset($id), function($query) use($id){
            return $query->where('id', '!=', $id);
        })
        ->whereNull('status')
        ->where('dia', $dia)
        ->where(function($q) use($cpfs) {
            $q->whereHas('representante', function($query) use($cpfs){
                $query->whereIn('cpf_cnpj', $cpfs);
            })
            ->orWhere('tipo_sala', 'reuniao');
        })
        ->orderBy('dia')
        ->orderBy('periodo_todo', 'DESC')
        ->get();        
        
        foreach ($agendados as $key => $value) {
            $temp = array();
            $almoco = $value->sala->horaAlmoco();
            $tipo_periodo = $periodo[0] <= $almoco ? 'manha' : 'tarde';
            $tipo_periodo_agendado = $value->inicioDoPeriodo() <= $almoco ? 'manha' : 'tarde';

            if($value->representante->tipoPessoa() == 'PF')
                $temp = array_intersect($cpfs, [apenasNumeros($value->representante->cpf_cnpj)]);
            if($value->isReuniao()){
                $participantes = array_keys(json_decode($value->participantes, true));
                $temp = array_merge($temp, array_intersect($cpfs, $participantes));
            }
            if(!empty($temp))
            {
                $periodo_inicio_agendado = Carbon::parse($value->inicioDoPeriodo());
                $periodo_final_agendado = Carbon::parse($value->fimDoPeriodo());

                if($value->periodo_todo && ($tipo_periodo_agendado == $tipo_periodo))
                    $vetados = array_merge($vetados, $temp);
                elseif(!$value->periodo_todo && $periodoTodo && ($tipo_periodo_agendado == $tipo_periodo))
                    $vetados = array_merge($vetados, $temp);
                elseif(!$value->periodo_todo && !$periodoTodo)
                    $periodo_inicio_agendado->addMinute()->between($periodo[0], $periodo[1]) || $periodo_final_agendado->subMinute()->between($periodo[0], $periodo[1]) ? 
                    $vetados = array_merge($vetados, $temp) : null;
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

    public function getTextoRotina($suspenso = false, $dataFinal = null, $user = null)
    {
        $opcao = ' foi suspenso automaticamente por 30 dias';
        $final = ' a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now());

        if(isset($user))
        {
            $texto = '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - Após análise da justificativa enviada pelo representante, o agendamento com o protocolo '. $this->protocolo;
            $texto .= ' teve o status atualizado para ' . self::STATUS_NAO_COMPARECEU . ' devido a recusa.';
            $texto .= ' A justificativa do funcionário foi enviada por e-mail para o representante e está no agendamento. Então, o CPF / CNPJ ';
        }else{
            $texto = '[Rotina Portal - Sala de Reunião] | [Ação - suspensão] - Após verificação dos agendamentos, o agendamento com o protocolo '. $this->protocolo;
            $texto .= ' teve o status atualizado para ' . self::STATUS_NAO_COMPARECEU . ' devido ao não envio de justificativa. Então, o CPF / CNPJ ';
        }

        $texto .= $this->representante->cpf_cnpj;
        if($suspenso)
            $texto .= isset($dataFinal) ? $opcao . $final : ' foi mantida a suspensão por tempo indeterminado' . $final;
        else
            $texto .= $opcao . $final;
        
        return $texto;
    }

    public function updateRotina($user = null)
    {
        $texto = null;
        $this->update([
            'status' => self::STATUS_NAO_COMPARECEU
        ]);

        $dados = [
            'idrepresentante' => $this->idrepresentante,
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'idusuario' => isset($user) ? $user->idusuario : null,
        ];
        $suspenso = $this->representante->suspensao();

        if(!isset($suspenso)){
            $texto = $this->getTextoRotina(false, null, $user);
            $dados['justificativa'] = json_encode([$texto], JSON_FORCE_OBJECT);
            $this->suspenso()->create($dados);
        }
        else{
            $texto = $this->getTextoRotina(true, $suspenso->data_final, $user);
            $dados['data_final'] = isset($suspenso->data_final) ? $dados['data_final'] : null;
            $dados['justificativa'] = $suspenso->addJustificativa($texto);
            $dados['agendamento_sala_id'] = $this->id;
            $suspenso->update($dados);
        }

        return $texto;
    }
}
