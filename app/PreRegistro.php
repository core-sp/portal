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
            // case 'contabil':
            //     $this->touch();
            //     break;
            case 'pessoaJuridica.responsavelTecnico':
                $valido = $this->pessoaJuridica->responsavelTecnico->validarUpdateAjax($campo, $valor);
                if(isset($valido))
                    $resultado = $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido->id]);
                else
                {
                    $rt = $this->pessoaJuridica->responsavelTecnico;
                    $rt->update([$campo => $valor]);
                    $this->touch();
                }
                $resultado = $valido;
                break;
        }

        return isset($resultado) ? $resultado : null;
    }

    public function criarAjax($classe, $campo, $valor)
    {
        $resultado = null;

        switch ($classe) {
            case 'pessoaJuridica.responsavelTecnico':
                $valido = 'App\ResponsavelTecnico'::buscarRT($valor);
                if(isset($valido))
                    $resultado = $this->pessoaJuridica->update(['responsavel_tecnico_id' => $valido->id]);
                $resultado = $valido;
                break;
            // case 'contabil':
            //     break;
            case 'anexos':
                $this->anexos()->create([$campo => $valor]);
                break;
        }

        return isset($resultado) ? $resultado : null;
    }
}
