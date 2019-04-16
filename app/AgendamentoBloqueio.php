<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendamentoBloqueio extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'idagendamentobloqueio';
    protected $table = 'agendamento_bloqueios';

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }
}
