<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Anexo extends Model
{
    protected $table = 'anexos';
    protected $guarded = [];
    protected $touches = ['preRegistros'];

    const TOTAL_PRE_REGISTRO = 5;
    const PATH_PRE_REGISTRO = 'userExterno/pre_registros';

    // seguir ordem de apresentação dos campos nas blades
    public static function codigosPreRegistro()
    {
        return [
            '7.1' => 'path',
        ];
    }

    public function preRegistros()
    {
        return $this->belongsTo('App\PreRegistro');
    }

    public static function armazenar($total, $valor)
    {
        if($total < Anexo::TOTAL_PRE_REGISTRO)
        {
            $nome = (string) Str::uuid() . '.' . $valor->extension();
            return $valor->storeAs(Anexo::PATH_PRE_REGISTRO, $nome, 'local');
        }

        return null;
    }
}
