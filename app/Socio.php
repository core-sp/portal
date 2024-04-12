<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Socio extends Model
{
    use SoftDeletes;

    protected $table = 'socios';
    protected $guarded = [];

    public static function camposPreRegistro()
    {
        return [
            'cpf_cnpj',
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'identidade',
            'orgao_emissor',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            'nacionalidade',
            'naturalidade_estado',
        ];
    }

    private function validarUpdateAjax($campo, $valor, $gerenti, $canEdit = null)
    {
        if($campo == 'cpf_cnpj')
        {
            if(isset($valor) && ((strlen($valor) == 11) || (strlen($valor) == 14))) 
                return self::buscar($valor, $gerenti, $canEdit);
            return 'remover';
        }

        return null;
    }

    private function updateAjax($campo, $valor)
    {
        if($campo != 'cpf_cnpj')
            $this->update([$campo => $valor]);
    }

    protected static function criarFinal($campo, $valor, $gerenti, $pr)
    {
        $valido = $campo == 'cpf_cnpj' ? self::buscar($valor, $gerenti, $pr->pessoaJuridica->getHistoricoCanEdit(self::class)) : null;
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pr->pessoaJuridica->getNextUpdateHistorico(self::class)];
            else{
                $pr->pessoaJuridica->socios()->attach($valido->id);
                $pr->pessoaJuridica->update(['historico_socio' => $pr->pessoaJuridica->setHistorico(self::class)]);
            }
        }

        return $valido;
    }

    public function pessoasJuridicas()
    {
        return $this->belongsToMany('App\PreRegistroCnpj', 'socio_pre_registro_cnpj', 'socio_id', 'pre_registro_cnpj_id');
    }

    public function atualizarFinal($campo, $valor, $gerenti, $pj)
    {
        $valido = $this->validarUpdateAjax($campo, $valor, $gerenti, $pj->getHistoricoCanEdit(self::class));
        if(isset($valido))
        {
            if($valido == 'notUpdate')
                $valido = ['update' => $pj->getNextUpdateHistorico(self::class)];
            elseif($valido == 'remover')
                $this->pessoasJuridicas()->detach($pj->id);
        }
        else
        {
            $this->updateAjax($campo, $valor);
            $pj->preRegistro->touch();
        }

        return $valido;
    }

    public static function buscar($cpf_cnpj, $gerenti, $canEdit = null)
    {
        if(isset($cpf_cnpj) && ((strlen($cpf_cnpj) == 11) || (strlen($cpf_cnpj) == 14)))
        {   
            if(isset($canEdit) && !$canEdit)
                return 'notUpdate';

            $existe = self::where('cpf_cnpj', $cpf_cnpj)->first();

            if(!isset($existe))
                $existe = isset($gerenti["registro"]) ? self::create($gerenti) : self::create(['cpf_cnpj' => $cpf_cnpj]);

            return $existe;
        }

        return null;
    }
}
