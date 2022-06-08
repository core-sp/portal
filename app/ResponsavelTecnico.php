<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponsavelTecnico extends Model
{
    use SoftDeletes;

    protected $table = 'responsaveis_tecnicos';
    protected $guarded = [];

    // RT = registro responsavel técnico
    public static function codigosPreRegistro()
    {
        return [
            'RT01' => 'nome',
            'RT02' => 'nome_social',
            'RT03' => 'registro',
            'RT04' => 'cpf',
            'RT05' => 'cep',
            'RT06' => 'logradouro',
            'RT07' => 'numero',
            'RT08' => 'complemento',
            'RT09' => 'bairro',
            'RT10' => 'cidade',
            'RT11' => 'uf',
            'RT12' => 'nome_mae',
            'RT13' => 'nome_pai',
            'RT14' => 'identidade',
            'RT15' => 'orgao_emissor',
            'RT16' => 'dt_expedicao',
            'RT17' => 'dt_nascimento',
            'RT18' => 'sexo'
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