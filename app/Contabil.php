<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contabil extends Model
{
    use SoftDeletes;

    protected $table = 'contabeis';
    protected $guarded = [];

    public static function camposPreRegistro()
    {
        return [
            'c1' => 'cnpj',
            'c2' => 'nome',
            'c3' => 'email',
            'c4' => 'nome_contato',
            'c5' => 'telefone'
        ];
    }

    public function preRegistros()
    {
        return $this->hasMany('App\PreRegistro')->withTrashed();
    }

    public static function buscar($cnpj)
    {
        if(isset($cnpj) && (strlen($cnpj) == 14))
        {
            $existe = Contabil::where('cnpj', $cnpj)->first();

            return isset($existe) ? $existe : Contabil::create(['cnpj' => $cnpj]);
        }

        return null;
    }

    public function validarUpdateAjax($campo, $valor)
    {
        if($campo == 'cnpj')
        {
            if(isset($valor) && (strlen($valor) == 14)) 
                return Contabil::buscar($valor);
            if(!isset($valor))
                return 'remover';
        }

        return null;
    }

    public function updateAjax($campo, $valor)
    {
        $this->update([$campo => $valor]);
    }

    public static function atualizar($arrayCampos)
    {
        if(isset($arrayCampos['cnpj']) && (strlen($arrayCampos['cnpj']) == 14))
        {
            $contabil = Contabil::buscar($arrayCampos['cnpj']);
            $contabil->update($arrayCampos);
            return $contabil;
        }

        return 'remover';
    }
}
