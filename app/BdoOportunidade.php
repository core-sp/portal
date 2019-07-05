<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BdoOportunidade extends Model
{
    use SoftDeletes;

	protected $primaryKey = 'idoportunidade';
    protected $table = 'bdo_oportunidades';
    protected $fillable = ['idempresa', 'titulo', 'segmento', 'regiaoatuacao', 'descricao', 'vagasdisponiveis', 'vagaspreenchidas', 'status', 'datainicio', 'idusuario'];

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }

    public function empresa()
    {
    	return $this->belongsTo('App\BdoEmpresa', 'idempresa');
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }
}
