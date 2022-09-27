<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'idnoticia';
    protected $guarded = [];

    public static function categorias()
    {
        return [
            'Benefícios',
            'Cotidiano',
            'Espaço do Contador',
            'Feiras',
            'Fiscalização',
        ];
    }
    
    public function regional()
    {
    	return $this->belongsTo('App\Regional', 'idregional');
    }

    public function curso()
    {
        return $this->belongsTo('App\Curso', 'idcurso');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }
}
