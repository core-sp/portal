<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idcurso';
    protected $table = 'cursos';
    protected $guarded = [];

    const ACESSO_PRI = 'Privado';
    const ACESSO_PUB = 'Público';

    const TIPO_CURSO = 'Curso';
    const TIPO_EVENTO = 'Evento Comemorativo';
    const TIPO_LIVE = 'Live';
    const TIPO_PALESTRA = 'Palestra';
    const TIPO_WORK = 'Workshop';

    public static function tipos()
    {
        return [
            self::TIPO_CURSO,
            self::TIPO_EVENTO,
            self::TIPO_LIVE,
            self::TIPO_PALESTRA,
            self::TIPO_WORK,
        ];
    }

    public static function acessos()
    {
        return [
            self::ACESSO_PRI,
            self::ACESSO_PUB,
        ];
    }

    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function cursoinscrito()
    {
    	return $this->hasMany('App\CursoInscrito', 'idcurso');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function noticia()
    {
        return $this->hasMany('App\Noticia', 'idcurso');
    }

    public function liberarAcesso($rep = false, $situacao = '')
    {
        return ($this->acesso == self::ACESSO_PUB) || (($this->acesso == self::ACESSO_PRI) && $rep && ($situacao == 'Situação: Em dia.'));
    }

    public function textoAcesso()
    {
        if($this->acesso == self::ACESSO_PUB)
            return 'Aberta ao público';
        if($this->acesso == self::ACESSO_PRI)
            return 'Restrita para representantes';
    }

    public function podeInscrever()
    {
        return $this->termino_inscricao >= now()->format('Y-m-d H:i');
    }
}
