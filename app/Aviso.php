<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Aviso extends Model
{
    protected $table = 'avisos';
    protected $guarded = [];

    const ATIVADO = 'Ativado';
    const DESATIVADO = 'Desativado';
    const COMPO_REP = 'representante';
    const COMPO_SMP = 'simples';

    public static function cores()
    {
        return [
            'light',
            'info',
            'warning',
            'primary',
            'success',
            'danger',
            'secondary',
            'dark'
        ];
    }

    public static function areas()
    {
        return [
            'Representante',
            'Balcão de Oportunidades',
            'Anuidade',
            'Agendamento',
        ];
    }

    public static function componente()
    {
        return [
            self::areas()[0] => self::COMPO_REP,
            self::areas()[1] => self::COMPO_SMP,
            self::areas()[2] => self::COMPO_SMP,
            self::areas()[3] => self::COMPO_SMP,
        ];
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function isAtivado()
    {
        return $this->status == Aviso::ATIVADO;
    }

    public function isComponenteSimples()
    {
        return Aviso::componente()[$this->area] == self::COMPO_SMP;
    }

    public function formatDtAtivarToInput()
    {
        return isset($this->dia_hora_ativar) ? str_replace(' ', 'T', Carbon::create($this->dia_hora_ativar)->format('Y-m-d H:i')) : null;
    }

    public function formatDtDesativarToInput()
    {
        return isset($this->dia_hora_desativar) ? str_replace(' ', 'T', Carbon::create($this->dia_hora_desativar)->format('Y-m-d H:i')) : null;
    }
}
