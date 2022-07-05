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

    // seguir ordem de apresentação dos campos nas blades
    public static function codigosPreRegistro()
    {
        return [
            '2.1' => 'nome_social',
            '2.2' => 'sexo',
            '2.3' => 'dt_nascimento',
            '2.4' => 'estado_civil',
            '2.5' => 'nacionalidade',
            '2.6' => 'naturalidade',
            '2.7' => 'nome_mae',
            '2.8' => 'nome_pai',
            '2.9' => 'tipo_identidade',
            '2.10' => 'identidade',
            '2.11' => 'orgao_emissor',
            '2.12' => 'dt_expedicao'
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
