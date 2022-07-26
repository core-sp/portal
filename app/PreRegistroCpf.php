<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PreRegistroCpf extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros_cpf';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    public static function camposPreRegistro()
    {
        return [
            'pf1' => 'nome_social',
            'pf2' => 'sexo',
            'pf3' => 'dt_nascimento',
            'pf4' => 'estado_civil',
            'pf5' => 'nacionalidade',
            'pf6' => 'naturalidade_cidade',
            'pf7' => 'naturalidade_estado',
            'pf8' => 'nome_mae',
            'pf9' => 'nome_pai',
            'pf10' => 'tipo_identidade',
            'pf11' => 'identidade',
            'pf12' => 'orgao_emissor',
            'pf13' => 'dt_expedicao'
        ];
    }

    public function preRegistro()
    {
        return $this->belongsTo('App\PreRegistro')->withTrashed();
    }

    public function maisDe45Anos()
    {
        return $this->dt_nascimento <= Carbon::today()->subYears(46)->format('Y-m-d');
    }
}
