<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contabil extends Model
{
    use SoftDeletes;

    protected $table = 'contabeis';
    protected $guarded = [];
    protected $touches = ['preRegistros'];

    // RC = registro contábil
    public static function codigosPreRegistro()
    {
        return [
            'RC01' => 'nome',
            'RC02' => 'cnpj',
            'RC03' => 'email',
            'RC04' => 'nome_contato',
            'RC05' => 'telefone'
        ];
    }

    public function preRegistros()
    {
        return $this->hasMany('App\PreRegistro')->withTrashed();
    }
}
