<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\AlteracaoRC;

class BdoRepresentante extends Model
{
    use SoftDeletes;

    protected $table = 'bdo_representantes';
    protected $guarded = [];

    const STATUS_RC_CADASTRO = 'Em Andamento';
    const STATUS_RC_REMOVIDO = 'Removido';
    const STATUS_RC_PUBLICO = 'Publicado';

    const STATUS_ADMIN_ATEND = 'Aguardando Atendimento';
    const STATUS_ADMIN_FINAN = 'Aguardando Financeiro';
    const STATUS_ADMIN_FINAL = 'Em Andamento';

    const STATUS_ACAO_ACEITO = 'Aceito';
    const STATUS_ACAO_RECUSADO = 'Recusado';

    const FORMATO_DATA_NOW = 'Y-m-d H:i:s';

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante')->withTrashed();
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function alteracoesRC()
    {
        return $this->hasMany('App\AlteracaoRC', 'bdo_representante_id');
    }

    private function dadosParaAtendimento($segmento_gerenti, $seccional_gerenti)
    {
        $s = mb_strtoupper($this->segmento);
        $sec = mb_strtoupper(json_decode($this->regioes)->seccional);
        $alterar = null;

        if($s != $segmento_gerenti)
            $alterar = $this->alteracoesRC()->create([
                "informacao" => AlteracaoRC::camposBdoRC()[1], 
                "valor_antigo" => $segmento_gerenti, 
                "valor_atual" => $s
            ]);

        if($sec != $seccional_gerenti)
            $alterar = $this->alteracoesRC()->create([
                "informacao" => AlteracaoRC::camposBdoRC()[0], 
                "valor_antigo" => $seccional_gerenti, 
                "valor_atual" => $sec
            ]);

        if(!is_null($alterar))
            $this->update([
                'status->atendimento->status' => self::STATUS_ADMIN_ATEND, 
                'status->atendimento->atendente' => 0, 
                'status->atendimento->data' => now()->format(self::FORMATO_DATA_NOW)
            ]);
    }

    private function dadosParaFinanceiro($em_dia)
    {
        if(!$em_dia)
            $this->update([
                'status->financeiro->status' => self::STATUS_ADMIN_FINAN, 
                'status->financeiro->atendente' => 0, 
                'status->financeiro->data' => now()->format(self::FORMATO_DATA_NOW)
            ]);
    }

    private function atualizarFinal()
    {
        if(!isset(json_decode($this->status)->status_final))
        {
            $status_final = !empty(json_decode($this->status, true)) ? "" : self::STATUS_ADMIN_FINAL;
            return $this->update(['status->status_final' => $status_final, 'status->data' => now()->format(self::FORMATO_DATA_NOW)]);
        }
        
        if(empty(Arr::where(
            Arr::flatten(json_decode($this->status, true)), function ($value, $key) {
                return Str::contains($value, 'Aguardando');
            })
        ))
            return $this->update(['status->status_final' => self::STATUS_ADMIN_FINAL, 'status->data' => now()->format(self::FORMATO_DATA_NOW)]);
    }

    private function atendimentoOkOuRollback($campos_recusados)
    {
        $campos = [
            AlteracaoRC::camposBdoRC()[0] => 'regioes->seccional',
            AlteracaoRC::camposBdoRC()[1] => 'segmento'
        ];

        foreach($this->alteracoesRC as $campo)
        {
            $aceite = !in_array($campo->informacao, $campos_recusados);
            $campo->update(['aceito' => $aceite]);

            if(!$aceite)
                $this->update([$campos[$campo->informacao] => $campo->valor_antigo]);
        }
    }

    private function finalOkOuRollback($status)
    {
        if($status == self::STATUS_ACAO_ACEITO)
            $this->save();
    }

    private function acaoSetores($status, $user_id, $justificativa = null, $setor)
    {
        $update = [
            'status->' . $setor . '->status' => $status, 
            'status->' . $setor . '->atendente' => $user_id, 
            'status->' . $setor . '->data' => now()->format(self::FORMATO_DATA_NOW)
        ];

        if(isset($justificativa))
            $update['justificativas->' . $setor] = $justificativa;

        $ok = $this->update($update);

        if($ok)
            return $this->atualizarFinal();
    }

    private function acaoFinal($status, $user_id, $justificativa = null)
    {
        $update = [
            'status->status_final' => $status, 
            'idusuario' => $user_id, 
            'status->data' => now()->format(self::FORMATO_DATA_NOW)
        ];

        if(isset($justificativa))
            $update['justificativas->justificativa_final'] = $justificativa;

        return $this->update($update);
    }

    private function statusHTMLAdministrador()
    {
        $s = json_decode($this->status);
        $t = '';

        if($this->statusContemAtendimento())
            $t .= '<span class="badge badge-primary mr-2">' . str_replace(' ', '<br>', $s->atendimento->status) . '</span>';

        if($this->statusContemFinanceiro())
            $t .= '<span class="badge badge-secondary mr-2">' . str_replace(' ', '<br>', $s->financeiro->status) . '</span>';

        if($this->statusEtapaFinal())
            $t .= '<span class="badge badge-success mr-2">' . str_replace(' ', '<br>', $s->status_final) . '</span>';

        if($this->statusFinalizado())
            $t .= '<span class="badge badge-success mr-2">Final: ' . str_replace(' ', '<br>', $s->status_final) . '</span>';

        return $t;
    }

    public function aceitarOuRecusar($user_id, $dados)
    {
        $setor = $dados['setor'];
        $justificativa = $dados['justificativa'];
        $campos_recusados = $dados['campos_recusados'];

        $acao = $setor == 'final' ? 'acaoFinal' : 'acaoSetores';
        $ok_ou_rollback = $setor . 'OkOuRollback';
        $status = isset($justificativa) ? self::STATUS_ACAO_RECUSADO : self::STATUS_ACAO_ACEITO;

        if(method_exists($this, $ok_ou_rollback))
            call_user_func_array([$this, $ok_ou_rollback], ['campos_recusados' => $campos_recusados, 'status' => $status]);

        return call_user_func_array([$this, $acao], [$status, $user_id, $justificativa, $setor]);
    }

    public function statusRC()
    {
        $s = json_decode($this->status)->status_final;

        return ($s == "") || ($s == self::STATUS_ADMIN_FINAL) ? self::STATUS_RC_CADASTRO : $s;
    }

    public function statusHTMLAdmin($id_perfil)
    {
        switch ($id_perfil) {
            case 3:
                return json_decode($this->status)->status_final;
                break;
            case 6:
            case 8:
                return json_decode($this->status)->atendimento->status;
                break;
            case 16:
                return json_decode($this->status)->financeiro->status;
                break;
            default:
                return $this->statusHTMLAdministrador();
                break;
        }
    }

    public function btnAcaoHTMLAdmin($id_perfil)
    {
        switch ($id_perfil) {
            case 6:
            case 8:
                return $this->atendimentoPendente() ? 'Editar' : 'Ver';
                break;
            case 16:
                return $this->financeiroPendente() ? 'Editar' : 'Ver';
                break;
            case 3:
            default:
                return $this->statusFinalizado() ? 'Ver' : 'Editar';
                break;
        }
    }

    public function statusFinalizado()
    {
        return in_array(json_decode($this->status)->status_final, [self::STATUS_ACAO_ACEITO, self::STATUS_ACAO_RECUSADO]);
    }

    public function statusEtapaFinal()
    {
        return json_decode($this->status)->status_final == self::STATUS_ADMIN_FINAL;
    }

    public function statusContemAtendimento()
    {
        return isset(json_decode($this->status)->atendimento);
    }

    public function statusContemFinanceiro()
    {
        return isset(json_decode($this->status)->financeiro);
    }

    public function atendimentoPendente()
    {
        return ($this->alteracoesRC->count() > 0) && $this->statusContemAtendimento() && 
        (json_decode($this->status)->atendimento->status == self::STATUS_ADMIN_ATEND);
    }

    public function financeiroPendente()
    {
        return $this->statusContemFinanceiro() && (json_decode($this->status)->financeiro->status == self::STATUS_ADMIN_FINAN);
    }

    public function acaoAtendimentoFinalizada()
    {
        if($this->statusContemAtendimento() && !$this->atendimentoPendente()){
            $s = json_decode($this->status)->atendimento;
            $s->atendente = \App\User::withTrashed()->find($s->atendente)->nome;

            if($s->status == self::STATUS_ACAO_RECUSADO)
                $s->justificativa = json_decode($this->justificativas)->atendimento;

            return $s;
        }
    }

    public function acaoFinanceiroFinalizada()
    {
        if($this->statusContemFinanceiro() && !$this->financeiroPendente()){
            $s = json_decode($this->status)->financeiro;
            $s->atendente = \App\User::withTrashed()->find($s->atendente)->nome;

            if($s->status == self::STATUS_ACAO_RECUSADO)
                $s->justificativa = json_decode($this->justificativas)->financeiro;

            return $s;
        }
    }

    public function acaoFinalFinalizada()
    {
        $s = json_decode($this->status);
        
        if($this->statusFinalizado()){
            $s->atendente = $this->user->nome;

            if($s->status_final == self::STATUS_ACAO_RECUSADO)
                $s->justificativa = json_decode($this->justificativas)->justificativa_final;

            return $s;
        }
    }

    public function statusAcaoRealizadaHTML($setor)
    {
        $acao = 'acao' . ucfirst($setor) . 'Finalizada';
        $obj = call_user_func_array([$this, $acao], []);
        
        if(gettype($obj) != "object")
            return '';

        $texto = '';
        $padrao = '&nbsp;&nbsp;<i class="fas fa-user"></i>&nbsp;&nbsp;' . $obj->atendente;
        $padrao .= '&nbsp;&nbsp;|&nbsp;&nbsp;<i class="fas fa-clock"></i>&nbsp;&nbsp;' . formataData($obj->data);

        if(!isset($obj->justificativa)){
            $texto .= '<p class="border border-success rounded p-2 col-xl-6">';
            $texto .= '<i class="fas fa-check text-success"></i>';
            $texto .= '&nbsp;&nbsp;<strong>Aceito!</strong>' . $padrao;
        }
            
        if(isset($obj->justificativa)){
            $texto .= '<p class="border border-danger rounded p-2">';
            $texto .= '<i class="fas fa-times text-danger"></i>';
            $texto .= '&nbsp;&nbsp;<strong>Recusado!</strong>' . $padrao;
            $texto .= '&nbsp;&nbsp;|&nbsp;&nbsp;<i class="far fa-comment-dots"></i>&nbsp;&nbsp;' . $obj->justificativa;
        }

        return $texto . '</p>';
    }

    public function setores($dados)
    {
        $this->dadosParaAtendimento($dados['segmento_gerenti'], $dados['seccional_gerenti']);
        $this->dadosParaFinanceiro($dados['em_dia']);

        return $this->atualizarFinal();
    }
}
