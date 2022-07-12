<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PreRegistroCnpj extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros_cnpj';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    const TOTAL_HIST = 1;

    private function horaUpdateHistorico()
    {
        $update = $this->getHistoricoArray()['update'];
        $updateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $update);
        $updateCarbon->addDay()->addHour();
        $updateCarbon->subMinutes($updateCarbon->minute);

        return $updateCarbon;
    }

    public static function camposPreRegistro()
    {
        return [
            'pj1' => 'razao_social',
            'pj2' => 'capital_social',
            'pj3' => 'nire',
            'pj4' => 'tipo_empresa',
            'pj5' => 'dt_inicio_atividade',
            'pj6' => 'inscricao_municipal',
            'pj7' => 'inscricao_estadual',
            'pj8' => 'cep',
            'pj9' => 'bairro',
            'pj10' => 'logradouro',
            'pj11' => 'numero',
            'pj12' => 'complemento',
            'pj13' => 'cidade',
            'pj14' => 'uf',
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

    public function canUpdateStatus()
    {
        return isset($this->responsavelTecnico->registro) && (strlen($this->responsavelTecnico->registro) > 4);
    }

    public function getHistoricoCanEdit()
    {
        $array = $this->getHistoricoArray();
        $can = intval($array['tentativas']) < PreRegistroCnpj::TOTAL_HIST;
        $horaUpdate = $this->horaUpdateHistorico();

        return $can || (!$can && ($horaUpdate < now()));
    }

    public function getHistoricoArray()
    {
        return json_decode($this->historico_rt, true);
    }

    public function getNextUpdateHistorico()
    {
        return $this->horaUpdateHistorico()->format('d\/m\/Y, \à\s H:i');
    }

    public function setHistorico()
    {
        $array = $this->getHistoricoArray();
        $totalTentativas = intval($array['tentativas']) < PreRegistroCnpj::TOTAL_HIST;

        if($totalTentativas)
            $array['tentativas'] = intval($array['tentativas']) + 1;
        $array['update'] = now()->format('Y-m-d H:i:s');

        return json_encode($array, JSON_FORCE_OBJECT);
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
