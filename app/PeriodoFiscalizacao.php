<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodoFiscalizacao extends Model
{
    protected $table = 'periodos_fiscalizacao';
    protected $guarded = [];

    const STATUS_PUBLICADO = 'Publicado';
    const STATUS_NAO_PUBLICADO = 'Não Publicado';

    public function dadoFiscalizacao()
    {
    	return $this->hasMany('App\DadoFiscalizacao', 'idperiodo');
    }

    public function somaTotal()
    {
        if(isset($this->dadoFiscalizacao))
            return $this->dadoFiscalizacao->makeHidden(['id', 'idregional', 'regional', 'idperiodo', 'created_at', 'updated_at'])->sum(function ($value) {
                return $value->somaTotal();
            });
    }
}
