<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idagendamento';
    protected $table = 'agendamentos';

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }
}
