<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class SuspensaoExcecao extends Model
{
    use SoftDeletes;

    protected $table = 'suspensoes_excecoes';
    protected $guarded = [];

    const SITUACAO_SUSPENSAO = 'Suspenso';
    const SITUACAO_EXCECAO = 'Liberado Temporariamente';

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function agendamento()
    {
    	return $this->belongsTo('App\AgendamentoSala', 'agendamento_sala_id');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public static function existeSuspensao($cpf_cnpj)
    {
    	return self::with('representante')
        ->where('situacao', self::SITUACAO_SUSPENSAO)
        ->where('cpf_cnpj', $cpf_cnpj)
        ->orWhereHas('representante', function($query) use($cpf_cnpj) {
            $query->where('cpf_cnpj', $cpf_cnpj);
        })
        ->first();
    }

    public function getCpfCnpj()
    {
    	return isset($this->representante) ? $this->representante->cpf_cnpj : formataCpfCnpj($this->cpf_cnpj);
    }

    public function isExcecao()
    {
    	return isset($this->data_inicial_excecao) && isset($this->data_final_excecao);
    }

    public function isLiberadoHoje()
    {
    	return $this->isExcecao() && ((now()->format('Y-m-d') >= $this->data_inicial_excecao) && (now()->format('Y-m-d') <= $this->data_final_excecao));
    }

    public function isSuspenso()
    {
    	return $this->situacao == self::SITUACAO_SUSPENSAO;
    }

    public function getSituacaoHTML()
    {
    	return $this->isSuspenso() ? '<span class="text-danger"><b>'.self::SITUACAO_SUSPENSAO.'</b></span>' : 
        '<span class="text-success"><b>'.self::SITUACAO_EXCECAO.'<b></span>';
    }

    public function mostraPeriodo()
    {
        $dataFinal = isset($this->data_final) ? onlyDate($this->data_final) : 'Tempo Indeterminado';

    	return onlyDate($this->data_inicial).' - '.$dataFinal;
    }

    public function mostraPeriodoExcecao()
    {
        if($this->isExcecao())
    	    return onlyDate($this->data_inicial_excecao).' - '.onlyDate($this->data_final_excecao);
        return '-----';
    }

    public function mostraPeriodoEmDias()
    {
        $dataInicial = Carbon::parse($this->data_inicial);

        $texto = isset($this->data_final) ? Carbon::parse($this->data_final)->diffInDays($dataInicial) : '-----';

        return 'Suspenso por ' . $texto . ' dias';
    }

    public function mostraPeriodoExcecaoEmDias()
    {
        if($this->isExcecao())
        {
            $dataInicial = Carbon::parse($this->data_inicial_excecao);
            $texto = Carbon::parse($this->data_final_excecao)->diffInDays($dataInicial);
            return 'Liberado por ' . $texto . ' dias';
        }
        return '';
    }

    public function getJustificativas()
    {
        return isset($this->justificativa) ? json_decode($this->justificativa, true) : array();
    }

    public function getJustificativasDesc()
    {
        return array_reverse($this->getJustificativas());
    }

    public function addJustificativa($texto)
    {
        $justificativas = $this->getJustificativas();
        array_push($justificativas, $texto);

        return json_encode($justificativas, JSON_FORCE_OBJECT);
    }

    public function getProtocolosDasJustificativas()
    {
        $protocolos = array();
        $justificativas = $this->getJustificativas();
        foreach($justificativas as $justificativa)
        {
            $posicao = strpos($justificativa, 'RC-AGE-');
            $posicao !== false ? array_push($protocolos, substr($justificativa, $posicao, 15)) : null;
        }

        return array_unique($protocolos);
    }

    public function addDiasDataFinal($dias)
    {
        if(isset($this->data_final))
            return Carbon::parse($this->data_final)->addDays($dias)->format('Y-m-d');
        return Carbon::parse($this->data_inicial)->addDays($dias)->format('Y-m-d');
    }

    public function getDataFinal()
    {
        return isset($this->data_final) ? Carbon::parse($this->data_final)->format('d/m/Y') : 'Tempo Indeterminado';
    }
}
