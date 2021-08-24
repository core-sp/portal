<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitaCedula extends Model
{
    protected $table = 'solicitacoes_cedulas';
    protected $guarded = [];
    protected $with = ['representante', 'usuario', 'regional'];

    const STATUS_EM_ANDAMENTO = "Em andamento";
    const STATUS_ACEITO = "Aceito";
    const STATUS_RECUSADO = "Recusado";

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function usuario()
    {
    	return $this->belongsTo('App\User', 'idusuario');
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }
}
