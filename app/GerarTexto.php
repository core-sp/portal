<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GerarTexto extends Model
{
    protected $table = 'gerar_textos';
    protected $guarded = [];

    public static function tipos()
    {
        return [
            'Título',
            'Subtítulo',
        ];
    }

    public static function tipos_doc()
    {
        return [
            'Carta de serviços ao usuário',
        ];
    }
}
