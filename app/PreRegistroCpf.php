<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreRegistroCpf extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros_cpf';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    // RF = registro pessoa física
    public static function codigosPreRegistro()
    {
        return [
            'RF01' => 'nome_social',
            'RF02' => 'sexo',
            'RF03' => 'dt_nascimento',
            'RF04' => 'estado_civil',
            'RF05' => 'nacionalidade',
            'RF06' => 'naturalidade',
            'RF07' => 'nome_mae',
            'RF08' => 'nome_pai',
            'RF09' => 'identidade',
            'RF10' => 'orgao_emissor',
            'RF11' => 'dt_expedicao'
        ];
    }

    public function preRegistro()
    {
        return $this->belongsTo('App\PreRegistro')->withTrashed();
    }
}
