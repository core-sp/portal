<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    const STATUS_ADMIN_COMUN = 'Em Andamento';

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
            $alterar = $this->alteracoesRC()->create(["informacao" => "SEGMENTO", "valor_antigo" => $segmento_gerenti, "valor_atual" => $s]);

        if($sec != $seccional_gerenti)
            $alterar = $this->alteracoesRC()->create(["informacao" => "REGIONAL", "valor_antigo" => $seccional_gerenti, "valor_atual" => $sec]);

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
        $status_final = !empty(json_decode($this->status, true)) ? "" : self::STATUS_ADMIN_COMUN;

        return $this->update(['status->status_final' => $status_final, 'status->data' => now()->format(self::FORMATO_DATA_NOW)]);
    }

    private function justificarAtendimento()
    {
        // Se recusado: 
            // incluir no status: {..."atendimento":{"status":"Recusado", "atendente":555, "data":"timestamp"}}
            // incluir na justificativa: {..."atendimento":{"texto":"gdgdgdgdg","data":"timestamp"}}
        // atualizarFinal()
    }

    private function justificarFinanceiro()
    {
        // Se recusado: 
            // incluir no status: {..."financeiro":{"status":"Recusado", "atendente":555, "data":"timestamp"}}
            // incluir na justificativa: {..."financeiro":{"texto":"gdgdgdgdg","data":"timestamp"}}
        // atualizarFinal()
    }

    private function aprovarAtendimento()
    {
        // incluir no status: {..."atendimento":{"status":"Aceito", "atendente":5555, "data":"timestamp"}}
        // atualizarFinal()
    }

    private function aprovarFinanceiro()
    {
        // incluir no status: {..."financeiro":{"status":"Aceito", "atendente":5555, "data":"timestamp"}}
        // atualizarFinal()
    }

    private function aprovarFinal()
    {
        // Se aceito:
            // incluir no status: {"status_final":"Aceito", "data":"timestamp"}
            // ID do usuario
        // Senão:
            // incluir no status: {"status_final":"Recusado", "data":"timestamp"}
            // incluir na justificativa: {"justificativa_final":{"texto":"gdgdgdgdg","data":"timestamp"}}
    }

    public function statusRC()
    {
        $s = json_decode($this->status)->status_final;

        return ($s == "") || ($s == self::STATUS_ADMIN_COMUN) ? self::STATUS_RC_CADASTRO : $s;
    }

    public function setores($dados)
    {
        $this->dadosParaAtendimento($dados['segmento_gerenti'], $dados['seccional_gerenti']);
        $this->dadosParaFinanceiro($dados['em_dia']);

        return $this->atualizarFinal();
    }
}
