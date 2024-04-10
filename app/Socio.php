<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Socio extends Model
{
    use SoftDeletes;

    protected $table = 'socios';
    protected $guarded = [];

    public static function camposPreRegistro()
    {
        return [
            'cpf_cnpj',
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'identidade',
            'orgao_emissor',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            'nacionalidade',
            'naturalidade_estado',
        ];
    }

    public function pessoasJuridicas()
    {
        return $this->belongsToMany('App\PreRegistroCnpj', 'socio_pre_registro_cnpj', 'socio_id', 'pre_registro_cnpj_id')->withPivot('historico_socio');
    }
}
