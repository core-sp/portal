<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idagendamento';
    protected $table = 'agendamentos';
    protected $fillable = ['nome', 'cpf', 'email', 'celular', 'dia', 'hora', 'protocolo', 'tiposervico', 'idregional', 'idusuario', 'status'];
    protected $with = ['user', 'regional'];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }
}
