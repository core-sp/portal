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
    const TOTAL_HIST_SOCIO = 10;

    private function horaUpdateHistorico($classe = null)
    {
        $update = $this->getHistoricoArray($classe)['update'];
        $updateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $update);

        switch ($classe) {
            case 'App\Socio':
                return $updateCarbon->addDays(2);
                break;
            default:
                return $updateCarbon->addDay();
        }
    }

    private function validarUpdateAjax($campo, $valor)
    {
        if(($campo == 'checkEndEmpresa') && ($valor == 'on'))
            return $this->preRegistro->getEndereco();
        return [$campo => $valor];
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

    public function socios()
    {
        return $this->belongsToMany('App\Socio', 'socio_pre_registro_cnpj', 'pre_registro_cnpj_id', 'socio_id')->withTimestamps();
    }

    public function podeCriarSocio()
    {
        return $this->socios->count() < self::TOTAL_HIST_SOCIO;
    }

    public function canUpdateStatus()
    {
        return isset($this->responsavelTecnico->registro) && (strlen($this->responsavelTecnico->registro) > 4);
    }

    public function possuiSocio()
    {
        return isset($this->socios) && $this->socios->isNotEmpty();
    }

    public function getHistoricoCanEdit($classe = null)
    {
        $array = $this->getHistoricoArray($classe);
        switch ($classe) {
            case 'App\Socio':
                $can = intval($array['tentativas']) < self::TOTAL_HIST_SOCIO;
                break;
            default:
                $can = intval($array['tentativas']) < self::TOTAL_HIST;
        }
        
        $horaUpdate = $this->horaUpdateHistorico($classe);

        return $can || (!$can && ($horaUpdate < now()));
    }

    public function getHistoricoArray($classe = null)
    {
        switch ($classe) {
            case 'App\Socio':
                return $this->fromJson(isset($this->historico_socio) ? $this->historico_socio : array());
                break;
            default:
                return $this->fromJson(isset($this->historico_rt) ? $this->historico_rt : array());
        }
    }

    public function getNextUpdateHistorico($classe = null)
    {
        return $this->horaUpdateHistorico($classe)->format('d\/m\/Y, \à\s H:i');
    }

    public function setHistorico($classe = null)
    {
        $array = $this->getHistoricoArray($classe);

        switch ($classe) {
            case 'App\Socio':
                $totalTentativas = intval($array['tentativas']) < self::TOTAL_HIST_SOCIO;
                break;
            default:
                $totalTentativas = intval($array['tentativas']) < self::TOTAL_HIST;
        }

        if($totalTentativas)
            $array['tentativas'] = intval($array['tentativas']) + 1;
        $array['update'] = now()->format('Y-m-d H:i:s');

        return $this->asJson($array);
    }

    public function getEndereco()
    {
        return $this->only(['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf']);
    }

    public function mesmoEndereco()
    {
        $naoNulo = isset($this->cep) && isset($this->logradouro) && isset($this->numero) && isset($this->bairro) && isset($this->cidade) && isset($this->uf);

        return $naoNulo && empty(array_diff_assoc($this->getEndereco(), $this->preRegistro->getEndereco()));
    }

    public function finalArray($arrayCampos)
    {
        if(isset($arrayCampos['checkEndEmpresa']))
        {
            if($arrayCampos['checkEndEmpresa'] == 'on')
                $arrayCampos = array_merge($arrayCampos, $this->preRegistro->getEndereco());
            unset($arrayCampos['checkEndEmpresa']);
        }

        return $this->update($arrayCampos);
    }
}
