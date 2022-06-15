<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponsavelTecnico extends Model
{
    use SoftDeletes;

    protected $table = 'responsaveis_tecnicos';
    protected $guarded = [];

    // seguir ordem de apresentação dos campos nas blades
    public static function codigosPreRegistro()
    {
        return [
            '5.1' => 'cpf',
            '5.2' => 'registro',
            '5.3' => 'nome',
            '5.4' => 'nome_social',
            '5.5' => 'dt_nascimento',
            '5.6' => 'sexo',
            '5.7' => 'identidade',
            '5.8' => 'orgao_emissor',
            '5.9' => 'dt_expedicao',
            '5.10' => 'cep',
            '5.11' => 'bairro',
            '5.12' => 'logradouro',
            '5.13' => 'numero',
            '5.14' => 'complemento',
            '5.15' => 'cidade',
            '5.16' => 'uf',
            '5.17' => 'nome_mae',
            '5.18' => 'nome_pai',
        ];
    }

    public function pessoasJuridicas()
    {
        return $this->hasMany('App\PreRegistroCnpj')->withTrashed();
    }

    public static function buscar($cpf, $gerenti)
    {
        if(isset($cpf) && (strlen($cpf) == 11))
        {   
            $existe = ResponsavelTecnico::where('cpf', $cpf)->first();

            if(!isset($existe))
                $existe = isset($gerenti["registro"]) ? ResponsavelTecnico::create($gerenti) : ResponsavelTecnico::create(['cpf' => $cpf]);

            return $existe;
        }

        return null;
    }

    public function validarUpdateAjax($campo, $valor, $gerenti)
    {
        if($campo == 'cpf')
        {
            if(isset($valor) && (strlen($valor) == 11)) 
                return ResponsavelTecnico::buscar($valor, $gerenti);
            if(!isset($valor))
                return 'remover';
        }

        return null;
    }

    public function updateAjax($campo, $valor)
    {
        $this->update([$campo => $valor]);
    }

    public static function atualizar($arrayCampos, $gerenti)
    {
        if(isset($arrayCampos['cpf']) && (strlen($arrayCampos['cpf']) == 11))
        {
            $rt = ResponsavelTecnico::buscar($arrayCampos['cpf'], $gerenti);
            $rt->update($arrayCampos);
            return $rt;
        }

        return 'remover';
    }
}
