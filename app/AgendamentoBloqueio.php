<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendamentoBloqueio extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idagendamentobloqueio';
    protected $table = 'agendamento_bloqueios';
    protected $guarded = [];

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function getMsgDiaTermino()
    {
        return isset($this->diatermino) ? onlyDate($this->diatermino) : 'Tempo Indeterminado';
    }
}
