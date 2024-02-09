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
        $updateCarbon->addDay();

        return $updateCarbon;
    }

    public function atualizarFinal($campo, $valor)
    {
        $valido = $this->validarUpdateAjax($campo, $valor);
        $this->update($valido);

        return null;
    }

    public static function camposPreRegistro()
    {
        return [
            'razao_social',
            'capital_social',
            'nire',
            'tipo_empresa',
            'dt_inicio_atividade',
            'nome_fantasia',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
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
        $can = intval($array['tentativas']) < self::TOTAL_HIST;
        $horaUpdate = $this->horaUpdateHistorico();

        return $can || (!$can && ($horaUpdate < now()));
    }

    public function getHistoricoArray()
    {
        return $this->fromJson(isset($this->historico_rt) ? $this->historico_rt : array());
    }

    public function getNextUpdateHistorico()
    {
        return $this->horaUpdateHistorico()->format('d\/m\/Y, \à\s H:i');
    }

    public function setHistorico()
    {
        $array = $this->getHistoricoArray();
        $totalTentativas = intval($array['tentativas']) < self::TOTAL_HIST;

        if($totalTentativas)
            $array['tentativas'] = intval($array['tentativas']) + 1;
        $array['update'] = now()->format('Y-m-d H:i:s');

        return $this->asJson($array);
    }

    public function mesmoEndereco()
    {
        $naoNulo = isset($this->cep) && isset($this->logradouro) && isset($this->numero) && isset($this->bairro) && isset($this->cidade) && isset($this->uf);
        
        $preRegistro = $this->preRegistro;
        $cepIgual = ($this->cep == $preRegistro->cep) && ($this->logradouro == $preRegistro->logradouro) && ($this->numero == $preRegistro->numero) && 
        ($this->complemento == $preRegistro->complemento) && ($this->bairro == $preRegistro->bairro) && ($this->cidade == $preRegistro->cidade) && ($this->uf == $preRegistro->uf);
        
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
