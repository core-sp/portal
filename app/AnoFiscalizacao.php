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

    public function dadoFiscalizacao()
    {
    	return $this->hasMany('App\DadoFiscalizacao', 'ano');
    }

    static $status_publicado = 'Publicado';
    static $status_nao_publicado = 'Não Publicado';
}
