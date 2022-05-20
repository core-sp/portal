<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreRegistro extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros';
    protected $guarded = [];

    // RG = registro geral
    public static function codigosPreRegistro()
    {
        return [
            'RG01' => 'ramo_atividade',
            'RG02' => 'segmento',
            'RG03' => 'registro_secundario',
            'RG04' => 'cep',
            'RG05' => 'logradouro',
            'RG06' => 'numero',
            'RG07' => 'complemento',
            'RG08' => 'bairro',
            'RG09' => 'cidade',
            'RG10' => 'uf',
            'RG11' => 'telefone',
            'RG12' => 'tipo_telefone',
            'RG13' => 'idregional'
        ];
    }

    public function userExterno()
    {
        return $this->belongsTo('App\UserExterno')->withTrashed();
    }

    public function regional()
    {
        return $this->belongsTo('App\Regional', 'idregional');
    }

    public function contabil()
    {
        return $this->belongsTo('App\Contabil')->withTrashed();
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'idusuario')->withTrashed();
    }

    public function pessoaFisica()
    {
        return $this->hasOne('App\PreRegistroCpf')->withTrashed();
    }

    public function pessoaJuridica()
    {
        return $this->hasOne('App\PreRegistroCnpj')->withTrashed();
    }

    public function anexos()
    {
        return $this->hasMany('App\Anexo');
    }

    private function validarUpdateAjax($campo, $valor)
    {
        $tipo = explode(';', $this->tipo_telefone);
        $tel = explode(';', $this->telefone);
        $tipo[1] = isset($tipo[1]) ? $tipo[1] : '';
        $tel[1] = isset($tel[1]) ? $tel[1] : '';

        if(($campo == 'tipo_telefone') || ($campo == 'telefone'))
        {
            if(strpos($valor, ';') !== false)
                $valor = $campo == 'tipo_telefone' ? $tipo[0].$valor : $tel[0].$valor;
            else
                $valor = $campo == 'tipo_telefone' ? $valor.';'.$tipo[1] : $valor.';'.$tel[1];
        }

        return [$campo => $valor];
    }

    public function atualizarAjax($classe, $campo, $valor)
    {
        $resultado = null;

        switch ($classe) {
            case 'preRegistro':
                $valido = $this->validarUpdateAjax($campo, $valor);
                $this->update($valido);
                break;
            case 'pessoaFisica':
                $this->pessoaFisica->update([$campo => $valor]);
                break;
            case 'pessoaJuridica':
                $valido = $this->pessoaJuridica->validarUpdateAjax($campo, $valor);
                $this->pessoaJuridica->update($valido);
                break;
            case 'contabil':
                $valido = $this->contabil->validarUpdateAjax($campo, $valor);
                if(isset($valido))
                    $resultado = $this->update(['contabil_id' => $valido == 'remover' ? null : $valido->id]);
                else
                {
                    $this->contabil->updateAjax($campo, $valor);
                    $this->touch();
                }
                $resultado = $valido;
                break;
            case 'pessoaJuridica.responsavelTecnico':
                $valido = $this->pessoaJuridica->responsavelTecnico->validarUpdateAjax($campo, $valor);
                if(isset($valido))
                    $resultado = $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido == 'remover' ? null : $valido->id]);
                else
                {
                    $this->pessoaJuridica->responsavelTecnico->updateAjax($campo, $valor);
                    $this->touch();
                }
                $resultado = $valido;
                break;
        }

        return $resultado;
    }

    public function criarAjax($classe, $relacao, $campo, $valor)
    {
        $resultado = null;

        switch ($relacao) {
            case 'pessoaJuridica.responsavelTecnico':
                $valido = $classe::buscar($valor);
                if(isset($valido))
                    $resultado = $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido->id]);
                $resultado = $valido;
                break;
            case 'contabil':
                $valido = $classe::buscar($valor);
                if(isset($valido))
                    $resultado = $this->update(['contabil_id' => $valido->id]);
                $resultado = $valido;
                break;
            case 'anexos':
                $anexos = $this->anexos();
                $valido = $classe::armazenar($anexos->count(), $valor);
                if(isset($valido))
                    $resultado = $anexos->create([$campo => $valido, 'nome_original' => $valor->getClientOriginalName()]);
                break;
        }

        return $valido;
    }
}
