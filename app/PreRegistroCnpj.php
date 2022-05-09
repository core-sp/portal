<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreRegistroCnpj extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros_cnpj';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    // RJ = registro pessoa jurídica
    public static function codigosPreRegistro()
    {
        return [
            'RJ01' => 'razao_social',
            'RJ02' => 'capital_social',
            'RJ03' => 'nire',
            'RJ04' => 'tipo_empresa',
            'RJ05' => 'dt_inicio_atividade',
            'RJ05' => 'inscricao_estadual',
            'RJ06' => 'inscricao_municipal',
            'RJ07' => 'cep',
            'RJ08' => 'logradouro',
            'RJ09' => 'numero',
            'RJ10' => 'complemento',
            'RJ11' => 'bairro',
            'RJ12' => 'cidade',
            'RJ13' => 'uf',
        ];
    }

    public function preRegistro()
    {
        return $this->belongsTo('App\PreRegistro')->withTrashed();
    }

    public function responsavelTecnico()
    {
        return $this->belongsTo('App\ResponsavelTecnico')->withTrashed();
    }
}
