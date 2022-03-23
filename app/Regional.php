<?php

namespace App;

use App\Repositories\AgendamentoBloqueioRepository;
use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    protected $table = 'regionais';
    protected $primaryKey = 'idregional';
    protected $guarded = [];
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany('App\User', 'idregional')->withTrashed();
    }

    public function noticias()
    {
        return $this->hasMany('App\Noticia', 'idregional');
    }

    public function agendamentos()
    {
        return $this->hasMany('App\Agendamento', 'idregional');
    }

    public function agendamentosBloqueios()
    {
        return $this->hasMany('App\AgendamentoBloqueio', 'idregional');
    }

    public function plantoesJuridicos()
    {
        return $this->hasMany('App\PlantaoJuridico', 'idregional');
    }

    public function horariosAge()
    {
        if(isset($this->horariosage))
            return explode(',', $this->horariosage);

        return [];
    }

    public function horariosDisponiveis($dia)
    {
        $horas = $this->horariosAge();
        $bloqueios = (new AgendamentoBloqueioRepository)->getByRegionalAndDay($this->idregional, $dia);
        if($bloqueios && $horas) {
            foreach($bloqueios as $bloqueio) {
                foreach($horas as $key => $hora) {
                    if($hora >= $bloqueio->horainicio && $hora <= $bloqueio->horatermino) {
                        unset($horas[$key]);
                    }
                }
            }
        }
        return $horas;
    }
}
