<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
