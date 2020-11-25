<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnoFiscalizacao extends Model
{
    protected $table = 'anos_fiscalizacao';
    protected $primaryKey = 'ano';
    protected $guarded = [];
    public $incrementing = false;
    protected $with = ['dadoFiscalizacao'];

    const STATUS_PUBLICADO = 'Publicado';
    const STATUS_NAO_PUBLICADO = 'Não Publicado';

    public function dadoFiscalizacao()
    {
    	return $this->hasMany('App\DadoFiscalizacao', 'ano');
    }
}
