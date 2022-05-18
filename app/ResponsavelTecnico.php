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

    public static function buscar($cpf)
    {
        // Buscar no Gerenti; se existir, traz os dados
        // e verifica se existe na tabela; se existir, atualiza
        // ou cria e devolve os dados para view do cliente
        $existe = ResponsavelTecnico::where('cpf', $cpf)->first();

        return isset($existe) ? $existe : ResponsavelTecnico::create(['cpf' => $cpf]);
    }

    public function validarUpdateAjax($campo, $valor)
    {
        if($campo == 'cpf')
        {
            if(isset($valor) && (strlen($valor) == 11) && ($valor != $this->cnpj)) 
                return $this->buscar($valor);
            if(!isset($valor))
                return 'remover';
        }

        return null;
    }
}
