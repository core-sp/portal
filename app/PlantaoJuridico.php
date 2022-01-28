<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlantaoJuridico extends Model
{
    protected $table = 'plantoes_juridicos';
    protected $guarded = [];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function temPlantaoJuridico()
    {
        return $this->qtd_advogados > 0 ? true : false;
    }
}
