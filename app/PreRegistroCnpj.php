<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PreRegistroCnpj extends Model
{
    use SoftDeletes;

    protected $table = 'pre_registros_cnpj';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    const TOTAL_HIST = 1;
    const TOTAL_HIST_SOCIO = 10;
    const TOTAL_HIST_DIAS_UPDATE = 1;
    const TOTAL_HIST_DIAS_UPDATE_SOCIO = 2;

    private function horaUpdateHistorico($classe = null)
    {
        $update = $this->getHistoricoArray($classe)['update'];
        $updateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $update);

        switch ($classe) {
            case 'App\Socio':
                return $updateCarbon->addDays(self::TOTAL_HIST_DIAS_UPDATE_SOCIO);
                break;
            default:
                return $updateCarbon->addDays(self::TOTAL_HIST_DIAS_UPDATE);
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
        return $this->belongsToMany('App\Socio', 'socio_pre_registro_cnpj', 'pre_registro_cnpj_id', 'socio_id')->withPivot('rt')->withTimestamps();
    }

    public function socioRT()
    {
        return $this->belongsToMany('App\Socio', 'socio_pre_registro_cnpj', 'pre_registro_cnpj_id', 'socio_id')->withPivot('rt')->withTimestamps()->wherePivot('rt', true);
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

    public function possuiRT()
    {
        return isset($this->responsavel_tecnico_id);
    }

    public function possuiRTSocio()
    {
        return $this->possuiSocio() && $this->possuiRT() && $this->socios->where('cpf_cnpj', $this->responsavelTecnico->cpf)->where('pivot.rt', true)->isNotEmpty();
    }

    public function possuiSocioPF()
    {
        if(!$this->possuiSocio())
            return false;

        $possuiPF = $this->socios->pluck('cpf_cnpj')->filter(function ($value, $key) {
            return strlen($value) == 11;
        })->isNotEmpty();

        return $this->possuiRTSocio() || $possuiPF;
    }

    public function possuiSocioBrasileiro()
    {
        return $this->possuiSocioPF() && $this->socios->where('nacionalidade', 'BRASILEIRA')->isNotEmpty();
    }

    public function possuiSocioReservista()
    {
        return $this->possuiSocioPF() && $this->socios->where('dt_nascimento', '<=', now()->subYears(45)->addDay()->format('Y-m-d'))->isNotEmpty();
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

    public function arrayValidacaoInputs()
    {
        $all = collect(Arr::except($this->attributesToArray(), ['id', 'historico_rt', 'historico_socio', 'responsavel_tecnico_id', 'pre_registro_id', 'created_at', 
        'updated_at', 'deleted_at']))->keyBy(function ($item, $key) {
            return in_array($key, array_keys($this->getEndereco())) ? $key . '_empresa' : $key;
        })->toArray();

        $all['checkEndEmpresa'] = $this->mesmoEndereco() ? 'on' : 'off';

        return $all;
    }

    // public function finalArray($arrayCampos)
    // {
    //     if(isset($arrayCampos['checkEndEmpresa']))
    //     {
    //         if($arrayCampos['checkEndEmpresa'] == 'on')
    //             $arrayCampos = array_merge($arrayCampos, $this->preRegistro->getEndereco());
    //         unset($arrayCampos['checkEndEmpresa']);
    //     }

    //     return $this->update($arrayCampos);
    // }
}
