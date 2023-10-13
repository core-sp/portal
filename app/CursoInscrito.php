<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CursoInscrito extends Model
{
    use SoftDeletes;
    
    protected $primaryKey = 'idcursoinscrito';
    protected $table = 'curso_inscritos';
    protected $fillable = ['cpf', 'nome', 'telefone', 'email',
    'registrocore', 'idcurso', 'presenca', 'tipo_inscrito', 'idusuario'];
    protected $with = ['curso'];

    const INSCRITO_FUN = 'Funcionário';
    const INSCRITO_AUT = 'Autoridade';
    const INSCRITO_CON = 'Convidado';
    const INSCRITO_PAR = 'Parceiro';
    const INSCRITO_SITE = 'Site';

    public static function tiposInscricao()
    {
        $tipos = [
            self::INSCRITO_FUN,
            self::INSCRITO_AUT,
            self::INSCRITO_CON,
            self::INSCRITO_PAR,
            self::INSCRITO_SITE,
        ];

        sort($tipos);

        return $tipos;
    }

    public function curso()
    {
    	return $this->belongsTo('App\Curso', 'idcurso');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'idusuario');
    }

    public function termos()
    {
        return $this->hasMany('App\TermoConsentimento', 'idcursoinscrito');
    }
}
