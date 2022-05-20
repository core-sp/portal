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

    public static function armazenar($total, $valor)
    {
        if($total < Anexo::TOTAL_PRE_REGISTRO)
        {
            $nome = (string) Str::uuid() . '.' . $valor->extension();
            return $valor->storeAs('userExterno/pre_registros', $nome);
        }

        return null;
    }
}
