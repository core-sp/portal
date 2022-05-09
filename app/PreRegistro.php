<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreRegistro extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros';
    protected $guarded = [];

    // RG = registro geral
    public static function codigosPreRegistro()
    {
        return [
            'RG01' => 'ramo_atividade',
            'RG02' => 'segmento',
            'RG03' => 'registro_secundario',
            'RG04' => 'cep',
            'RG05' => 'logradouro',
            'RG06' => 'numero',
            'RG07' => 'complemento',
            'RG08' => 'bairro',
            'RG09' => 'cidade',
            'RG10' => 'uf',
            'RG11' => 'telefone',
            'RG12' => 'tipo_telefone'
        ];
    }

    public function userExterno()
    {
        return $this->belongsTo('App\UserExterno')->withTrashed();
    }

    public function regional()
    {
        return $this->belongsTo('App\Regional', 'idregional');
    }

    public function contabil()
    {
        return $this->belongsTo('App\Contabil')->withTrashed();
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function pessoaFisica()
    {
        return $this->hasOne('App\PreRegistroCpf')->withTrashed();
    }

    public function pessoaJuridica()
    {
        return $this->hasOne('App\PreRegistroCnpj')->withTrashed();
    }

    public function anexos()
    {
        return $this->hasMany('App\Anexo');
    }
}
