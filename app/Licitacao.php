<?php

namespace App;

use App\Traits\ControleAcesso;
use App\Traits\TabelaAdmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Licitacao extends Model
{
	use SoftDeletes;

    protected $primaryKey = 'idlicitacao';
    protected $table = 'licitacoes';
    protected $fillable = ['modalidade', 'situacao', 'uasg', 'titulo', 'edital', 'nrlicitacao', 'nrprocesso', 'datarealizacao', 'objeto', 'idusuario'];

    // Modalidades de licitação
    const MOD_CARTA_CONVITE = 'Carta Convite';
    const MOD_CONCORRECIA_PUBLICA = 'Concorrência Pública';
    const MOD_CONCURSO = 'Concurso';
    const MOD_COTACAO_ELETRONICA = 'Cotação Eletrônica';
    const MOD_CREDENCIAMENTO = 'Credenciamento';
    const MOD_LEILAO = 'Leilão';
    const MOD_PREGAO_ELETRONICO_SRP = 'Pregão Eletrônico SRP';
    const MOD_PREGAO_ELETRONICO_TRADICIONAL = 'Pregão Eletrônico Tradicional';
    const MOD_PREGAO_PRESENCIAL = 'Pregão Presencial';
    const MOD_TOMADA_DE_PRECOS = 'Tomada de Preços';

    // Situações de licitação
    const SIT_ABERTO = 'Aberto';
    const SIT_ADJUDICADO = 'Adjudicado';
    const SIT_ANULADO = 'Anulado';
    const SIT_CANCELADO = 'Cancelado';
    const SIT_CONCLUIDO = 'Concluído';
    const SIT_DESERTO = 'Deserto';
    const SIT_EM_ANDAMENTO = 'Em Andamento';
    const SIT_EM_FASE_DE_RECURSO = 'Em fase de recurso';
    const SIT_ENCERRADO = 'Encerrado';
    const SIT_FASE_INTERNA = 'Fase interna';
    const SIT_HOMOLOGADO = 'Homologado';
    const SIT_SUSPENSO = 'Suspenso';

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public static function modalidadesLicitacao()
    {
        return [
            Licitacao::MOD_CARTA_CONVITE,
            Licitacao::MOD_CONCORRECIA_PUBLICA,
            Licitacao::MOD_CONCURSO,
            Licitacao::MOD_COTACAO_ELETRONICA,
            Licitacao::MOD_CREDENCIAMENTO,
            Licitacao::MOD_LEILAO,
            Licitacao::MOD_PREGAO_ELETRONICO_SRP,
            Licitacao::MOD_PREGAO_ELETRONICO_TRADICIONAL,
            Licitacao::MOD_PREGAO_PRESENCIAL,
            Licitacao::MOD_TOMADA_DE_PRECOS
        ];
    }

    public static function situacoesLicitacao()
    {
        return [
            Licitacao::SIT_ABERTO,
            Licitacao::SIT_ADJUDICADO,
            Licitacao::SIT_ANULADO,
            Licitacao::SIT_CANCELADO,
            Licitacao::SIT_CONCLUIDO,
            Licitacao::SIT_DESERTO,
            Licitacao::SIT_EM_ANDAMENTO,
            Licitacao::SIT_EM_FASE_DE_RECURSO,
            Licitacao::SIT_ENCERRADO,
            Licitacao::SIT_FASE_INTERNA,
            Licitacao::SIT_HOMOLOGADO,
            Licitacao::SIT_SUSPENSO
        ];
    }
}
