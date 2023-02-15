<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $table = 'avisos';
    protected $guarded = [];
    protected $with = ['user'];

    const ATIVADO = 'Ativado';
    const DESATIVADO = 'Desativado';

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

    public static function componente()
    {
        return [
            'Representante' => 'representante',
            'Balcão de Oportunidades' => 'simples'
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
}
