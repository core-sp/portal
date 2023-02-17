<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
            'Balcão de Oportunidades'
        ];
    }

    public static function componente()
    {
        return [
            self::areas()[0] => self::COMPO_REP,
            self::areas()[1] => self::COMPO_SMP
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
}
