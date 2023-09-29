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
    const TOTAL_DIAS_EXCECAO = 14;

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
        $cpf_cnpj = apenasNumeros($cpf_cnpj);

    	return self::with('representante')
        ->where('cpf_cnpj', $cpf_cnpj)
        ->orWhereHas('representante', function($query) use($cpf_cnpj) {
            $query->where('cpf_cnpj', $cpf_cnpj);
        })
        ->first();
    }

    public static function participantesSuspensos($cpfs)
    {
        $final_suspensos = array();
        foreach($cpfs as $chave => $cpf)
            $cpfs[$chave] = apenasNumeros($cpf);

    	$suspensos = self::with('representante')
        ->whereIn('cpf_cnpj', $cpfs)
        ->orWhereHas('representante', function($query) use($cpfs) {
            $query->whereIn('cpf_cnpj', $cpfs);
        })
        ->get();

        foreach($suspensos as $key => $value)
        {
            if(!$value->isLiberadoHoje())
                isset($value->representante) ? array_push($final_suspensos, apenasNumeros($value->representante->cpf_cnpj)) : 
                array_push($final_suspensos, $value->cpf_cnpj);
        }

        return $final_suspensos;
    }

    public function getTotalDiasExcecao()
    {
    	return self::TOTAL_DIAS_EXCECAO;
    }

    public function getCpfCnpj()
    {
    	return isset($this->representante) ? $this->representante->cpf_cnpj : formataCpfCnpj($this->cpf_cnpj);
    }

    public function getTextoHTMLSeCadastro()
    {
    	return isset($this->representante) ? '<br><small><em>Cadastrado no Portal</em></small>' : '';
    }

    public function possuiExcecao()
    {
    	return isset($this->data_inicial_excecao) && isset($this->data_final_excecao);
    }

    public function isLiberadoHoje()
    {
    	return $this->possuiExcecao() && (now()->format('Y-m-d') >= $this->data_inicial_excecao) && (now()->format('Y-m-d') <= $this->data_final_excecao);
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
        if($this->possuiExcecao())
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
        if($this->possuiExcecao())
        {
            $dataInicial = Carbon::parse($this->data_inicial_excecao);
            $texto = Carbon::parse($this->data_final_excecao)->diffInDays($dataInicial) + 1;
            return $texto == 1 ? 'Liberado por 1 dia' : 'Liberado por ' . $texto . ' dias';
        }
        return '';
    }

    public function getJustificativas()
    {
        return isset($this->justificativa) ? json_decode($this->justificativa, true) : array();
    }

    public function getJustificativasDesc($justificativas = null)
    {
        return isset($justificativas) ? array_reverse($justificativas) : array_reverse($this->getJustificativas());
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

    public function getJustificativasByAcao($acao)
    {
        if(!in_array($acao, ['suspensão', 'exceção']))
            return null;

        $justificadas = array();
        $justificativas = $this->getJustificativas();
        foreach($justificativas as $justificativa)
            strpos($justificativa, '[Ação - '.$acao.']') !== false ? array_push($justificadas, $justificativa) : null;

        return $justificadas;
    }

    public function removeNomeAcaoJustificativa($justificativa, $acao)
    {
        if(!in_array($acao, ['suspensão', 'exceção']))
            return null;

        $comprimento = $acao == 'suspensão' ? 25 : 23;
        $comprimento = strpos($justificativa, '|') + $comprimento;

        return substr_replace($justificativa, '', 0, $comprimento);
    }

    public function addDiasDataFinal($dias)
    {
        if(isset($this->data_final))
            return Carbon::parse($this->data_final)->addDays($dias)->format('Y-m-d');
        return now()->addDays($dias)->format('Y-m-d');
    }

    public function getDataFinal()
    {
        return isset($this->data_final) ? Carbon::parse($this->data_final)->format('d/m/Y') : 'Tempo Indeterminado';
    }

    public function getSituacaoUpdateExcecao($data_inicial, $data_final)
    {
        if(!isset($data_inicial) && !isset($data_final))
            return self::SITUACAO_SUSPENSAO;

        if(($data_inicial > now()->format('Y-m-d')) || ($data_final < now()->format('Y-m-d')))
            return self::SITUACAO_SUSPENSAO;

        return self::SITUACAO_EXCECAO;
    }

    public function updateRelacaoByIdRep($id)
    {
        if(!isset($this->representante))
            $this->update([
                'idrepresentante' => $id,
                'cpf_cnpj' => null
            ]);
        return $this->fresh();
    }
}
