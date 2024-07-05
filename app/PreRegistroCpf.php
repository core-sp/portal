<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Arr;

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
        return isset($this->dt_nascimento) && ($this->dt_nascimento < Carbon::today()->subYears(45)->format('Y-m-d'));
    }

    public function brasileira()
    {
        return $this->nacionalidade == 'BRASILEIRA';
    }

    public function reservista()
    {
        return ($this->sexo == 'M') && !$this->maisDe45Anos();
    }

    // public function finalArray($arrayCampos)
    // {
    //     return $this->update($arrayCampos);
    // }

    public function arrayValidacaoInputs()
    {
        return Arr::except($this->attributesToArray(), ['id', 'pre_registro_id', 'created_at', 'updated_at', 'deleted_at']);
    }
}
