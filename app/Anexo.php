<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// Talvez usar esse model para todas as funcionalidades que precisarem de anexos
// Concentra melhor as validações etc
class Anexo extends Model
{
    protected $table = 'anexos';
    protected $guarded = [];
    protected $touches = ['preRegistros'];

    // RA = registro anexo
    public static function codigosPreRegistro()
    {
        return [
            'RA01' => 'path',
        ];
    }

    public function preRegistros()
    {
        return $this->belongsTo('App\PreRegistro');
    }
}
