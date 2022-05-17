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
            'RJ06' => 'inscricao_estadual',
            'RJ07' => 'inscricao_municipal',
            'RJ08' => 'cep',
            'RJ09' => 'logradouro',
            'RJ10' => 'numero',
            'RJ11' => 'complemento',
            'RJ12' => 'bairro',
            'RJ13' => 'cidade',
            'RJ14' => 'uf',
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

    public function mesmoEndereco()
    {
        $preRegistro = $this->preRegistro;
        return ($this->cep == $preRegistro->cep) && ($this->numero == $preRegistro->numero) && ($this->complemento == $preRegistro->complemento);
    }

    public function validarUpdateAjax($campo, $valor)
    {
        if($campo == 'checkEndEmpresa')
            if($valor == 'on')
            {
                $preRegistro = $this->preRegistro;
                return [
                    'cep' => $preRegistro->cep, 
                    'logradouro' => $preRegistro->logradouro, 
                    'numero' => $preRegistro->numero, 
                    'complemento' => $preRegistro->complemento, 
                    'bairro' => $preRegistro->bairro, 
                    'cidade' => $preRegistro->cidade, 
                    'uf' => $preRegistro->uf
                ];
            }

        return [$campo => $valor];
    }
}
