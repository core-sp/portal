<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponsavelTecnico extends Model
{
    use SoftDeletes;

    protected $table = 'responsaveis_tecnicos';
    protected $guarded = [];

    public static function camposPreRegistro()
    {
        return [
            'cpf',
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'sexo',
            'tipo_identidade',
            'identidade',
            'orgao_emissor',
            'dt_expedicao',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            'titulo_eleitor',
            'zona',
            'secao',
            'ra_reservista',
        ];
    }

    public function pessoasJuridicas()
    {
        return $this->hasMany('App\PreRegistroCnpj')->withTrashed();
    }

    public static function buscar($cpf, $gerenti, $canEdit = null)
    {
        if(isset($cpf) && (strlen($cpf) == 11))
        {   
            if(isset($canEdit) && !$canEdit)
                return 'notUpdate';

            $existe = ResponsavelTecnico::where('cpf', $cpf)->first();

            if(!isset($existe))
                $existe = isset($gerenti["registro"]) ? ResponsavelTecnico::create($gerenti) : ResponsavelTecnico::create(['cpf' => $cpf]);

            return $existe;
        }

        return null;
    }

    public function validarUpdateAjax($campo, $valor, $gerenti, $canEdit = null)
    {
        if($campo == 'cpf')
        {
            if(isset($valor) && (strlen($valor) == 11)) 
                return ResponsavelTecnico::buscar($valor, $gerenti, $canEdit);
            return 'remover';
        }

        return null;
    }

    public function updateAjax($campo, $valor)
    {
        if($campo != 'cpf')
            $this->update([$campo => $valor]);
    }

    public static function atualizar($arrayCampos, $gerenti)
    {
        if(isset($arrayCampos['cpf']) && (strlen($arrayCampos['cpf']) == 11))
        {
            $rt = ResponsavelTecnico::buscar($arrayCampos['cpf'], $gerenti);
            unset($arrayCampos['cpf']);
            $rt->update($arrayCampos);
            return $rt;
        }

        return 'remover';
    }
}
