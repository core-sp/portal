<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgendamentoSala extends Model
{
    protected $table = 'agendamentos_salas';
    protected $guarded = [];

    public function sala()
    {
    	return $this->belongsTo('App\SalaReuniao', 'sala_reuniao_id');
    }

    public function representante()
    {
    	return $this->belongsTo('App\Representante', 'idrepresentante');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }
}
