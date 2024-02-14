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

    public function atualizarFinal($campo, $valor)
    {
        $this->update([$campo => $valor]);

        return null;
    }

    public static function camposPreRegistro()
    {
        return [
            'nome_social',
            'sexo',
            'dt_nascimento',
            'estado_civil',
            'nacionalidade',
            'naturalidade_cidade',
            'naturalidade_estado',
            'nome_mae',
            'nome_pai',
            'tipo_identidade',
            'identidade',
            'orgao_emissor',
            'dt_expedicao',
            'titulo_eleitor',
            'zona',
            'secao',
            'ra_reservista',
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

    public function finalArray($arrayCampos)
    {
        return $this->update($arrayCampos);
    }
}
