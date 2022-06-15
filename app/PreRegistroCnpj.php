<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreRegistroCnpj extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros_cnpj';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    // seguir ordem de apresentação dos campos nas blades
    public static function codigosPreRegistro()
    {
        return [
            '2.1' => 'razao_social',
            '2.2' => 'capital_social',
            '2.3' => 'nire',
            '2.4' => 'tipo_empresa',
            '2.5' => 'dt_inicio_atividade',
            '2.6' => 'inscricao_municipal',
            '2.7' => 'inscricao_estadual',
            '4.8' => 'cep',
            '4.9' => 'bairro',
            '4.10' => 'logradouro',
            '4.11' => 'numero',
            '4.12' => 'complemento',
            '4.13' => 'cidade',
            '4.14' => 'uf',
        ];
    }

    public function preRegistro()
    {
        return $this->belongsTo('App\PreRegistro')->withTrashed();
    }

    public function responsavelTecnico()
    {
        return $this->belongsTo('App\ResponsavelTecnico')->withTrashed();
    }

    public function mesmoEndereco()
    {
        $naoNulo = isset($this->cep) && isset($this->logradouro) && isset($this->numero) && isset($this->bairro) && isset($this->cidade) && isset($this->uf);
        
        $preRegistro = $this->preRegistro;
        $cepIgual = ($this->cep == $preRegistro->cep) && ($this->logradouro == $preRegistro->logradouro) && ($this->numero == $preRegistro->numero) && 
        ($this->bairro == $preRegistro->bairro) && ($this->cidade == $preRegistro->cidade) && ($this->uf == $preRegistro->uf);
        
        return $naoNulo && $cepIgual;
    }

    public function validarUpdateAjax($campo, $valor)
    {
        if($campo == 'checkEndEmpresa')
            if($valor == 'on')
            {
                $preRegistro = $this->preRegistro;
                return [
                    'cep' => $preRegistro->cep, 
                    'logradouro' => $preRegistro->logradouro, 
                    'numero' => $preRegistro->numero, 
                    'complemento' => $preRegistro->complemento, 
                    'bairro' => $preRegistro->bairro, 
                    'cidade' => $preRegistro->cidade, 
                    'uf' => $preRegistro->uf
                ];
            }

        return [$campo => $valor];
    }

    public function validarUpdate($arrayCampos)
    {
        if(isset($arrayCampos['checkEndEmpresa']))
        {
            if($arrayCampos['checkEndEmpresa'] == 'on')
            {
                $preRegistro = $this->preRegistro;
                $arrayCampos['cep'] = $preRegistro->cep;
                $arrayCampos['logradouro'] = $preRegistro->logradouro;
                $arrayCampos['numero'] = $preRegistro->numero;
                $arrayCampos['complemento'] = $preRegistro->complemento; 
                $arrayCampos['bairro'] = $preRegistro->bairro;
                $arrayCampos['cidade'] = $preRegistro->cidade; 
                $arrayCampos['uf'] = $preRegistro->uf;
            }
            unset($arrayCampos['checkEndEmpresa']);
        }

        return $arrayCampos;
    }
}
