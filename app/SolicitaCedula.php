<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitaCedula extends Model
{
    protected $table = 'solicita_cedula';
    protected $guarded = [];
    protected $with = ['representante'];

    const STATUS_EM_ANDAMENTO = "Em andamento";
    const STATUS_APROVADO = "Aprovado";
    const STATUS_REPROVADO = "Reprovado";

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }
}
