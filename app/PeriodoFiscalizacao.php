<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodoFiscalizacao extends Model
{
    protected $table = 'periodos_fiscalizacao';
    protected $guarded = [];
    // protected $with = ['dadoFiscalizacao'];

    const STATUS_PUBLICADO = 'Publicado';
    const STATUS_NAO_PUBLICADO = 'Não Publicado';

    public function dadoFiscalizacao()
    {
    	return $this->hasMany('App\DadoFiscalizacao', 'idperiodo');
    }
}
