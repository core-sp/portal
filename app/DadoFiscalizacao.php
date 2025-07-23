<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DadoFiscalizacao extends Model
{
    protected $table = 'dados_fiscalizacao';
    protected $guarded = [];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function ano()
    {
    	return $this->belongsTo('App\PeriodoFiscalizacao', 'idperiodo');
    }

    public function somaTotal()
    {
    	return collect($this->toArray())->values()->sum();
    }

    public static function campos()
    {
        return [
            "autoconstatacao" => 'Auto de Constatação',
            "autosdeinfracao" => 'Autos de Infração',
            "multaadministrativa" => 'Multa Administrativa',
            "processofiscalizacaopf" => 'Processos de Fiscalização<span class="invisible">F</span>',
            "processofiscalizacaopj" => 'Processos de Fiscalização<span class="invisible">J</span>',
            "registroconvertidopf" => 'Registros Convertidos<span class="invisible">F</span>',
            "registroconvertidopj" => 'Registros Convertidos<span class="invisible">J</span>',
            "processoverificacao" => 'Processos de Verificação',
            "dispensaregistro" => 'Dispensa de Registro (de ofício)',
            "notificacaort" => 'Notificações de RT',
            "orientacaorepresentada" => 'Orientações às representadas',
            "orientacaorepresentante" => 'Orientações aos representantes',
            "cooperacaoinstitucional" => 'Diligências externas',
            "orientacaocontabil" => 'Orientação às contabilidades',
            "oficioprefeitura" => 'Ofício às prefeituras',
            "oficioincentivo" => 'Ofício de incentivo a contratação de representantes comerciais',
            "notificacandidatoeleicao" => 'Notificação Candidatos Eleições',
        ];
    }

    public static function excecoesMapa()
    {
        return [
            'processofiscalizacaopf', 
            'processofiscalizacaopj', 
            'registroconvertidopf', 
            'dispensaregistro', 
            'orientacaorepresentante',
            "autoconstatacao",
        ];
    }

    public static function selectMapa()
    {
        $temp = collect(self::campos())->except(self::excecoesMapa())->keys()->all();

        return implode(',', $temp);
    }
}
