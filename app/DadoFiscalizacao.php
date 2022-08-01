<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DadoFiscalizacao extends Model
{
    protected $table = 'dados_fiscalizacao';
    protected $guarded = [];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function ano()
    {
    	return $this->belongsTo('App\PeriodoFiscalizacao', 'idperiodo');
    }
}
